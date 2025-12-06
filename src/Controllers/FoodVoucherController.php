<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Helpers\UserVouchers;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use ErrorException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
class FoodVoucherController extends BaseController
{
    use HasUserNotifications;

    protected array $foodVoucherApi;

    public function __construct(
        protected Authenticator $auth,
        protected GuzzleClient $guzzle,
        protected Config $config,
        protected LoggerInterface $log,
        protected Worklog $worklog,
        protected Redirector $redirect,
        protected Response $response,
        protected User $user,
        protected Translator $translator,
    ) {
        $this->foodVoucherApi = $this->config->get('food_voucher_api');
    }

    private function checkActive(): void
    {
        if (
            !$this->config->get('enable_voucher')
            || !$this->config->get('enable_force_food')
            || !$this->foodVoucherApi
            || !$this->foodVoucherApi['info_url']
            || !$this->foodVoucherApi['auth_token']
            || !$this->foodVoucherApi['post_url']
        ) {
            throw new HttpNotFound();
        }

        if (!$this->userIsCrew() && UserVouchers::eligibleVoucherCount($this->auth->user()) == 0) {
            throw new HttpForbidden();
        }
    }

    private function getAuthToken(): string
    {
        return $this->foodVoucherApi['auth_token'];
    }

    private function userIsCrew(): bool
    {
        $user = $this->auth->user();
        return
            $user
            && (
                $user->state->force_active && $this->config->get('enable_force_active')
                || $user->state->force_food && $this->config->get('enable_force_food')
            );
    }

    /**
     * @throws ErrorException
     */
    public function cacheInfo(): array
    {
        $infoUrl = $this->foodVoucherApi['info_url'];
        try {
            $response = $this->guzzle->get(
                $infoUrl,
                ['headers' =>
                    [
                        'Authorization' => 'Bearer ' . $this->getAuthToken(),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );
        } catch (ConnectException | GuzzleException $e) {
            $this->log->error('Exception during food voucher api request', ['exception' => $e]);
            throw new ErrorException('user.food.request-error');
        }
        if ($response->getStatusCode() !== 200) {
            $this->log->error(
                'Error response {code} from food voucher api: "{content}"',
                ['code' => $response->getStatusCode(), 'content' => $response->getBody()->getContents()]
            );
            throw new HttpNotFound('user.food.request-error');
        }
        return json_decode($response->getBody()->getContents(), true);
    }

    private function getInfo(bool $crew, ?array $userMealVouchers = null): array
    {
        $data = cache(
            'foodVoucherInfo',
            [$this, 'cacheInfo'],
            $this->foodVoucherApi['info_cache']
        );

        $now = Carbon::now();
        $locale = $this->translator->getLocale();
        $futureMeals = [];
        $gotMeals = [];

        foreach ($data as $id => $meal) {
            $endTime = Carbon::parse($meal['datetime']['date'] . ' ' . $meal['datetime']['end']);
            $startTime = Carbon::parse($meal['datetime']['date'] . ' ' . $meal['datetime']['start']);
            if (
                $now < $endTime
                && $now->copy()->addDays(2) >= $startTime
            ) {
                $sold_out = $crew
                    ? $meal['availability']['crew']
                    : $meal['availability']['regular'];
                $sold_out = $sold_out === false || $sold_out === 'false';

                $futureMeals[$id] = [
                    'id' => $id,
                    'name' => $meal['name'][substr($locale, 0, 2)],
                    'endtime' => $endTime,
                    'sold_out' => $sold_out,
                    'hidden' => $userMealVouchers && in_array($id, $userMealVouchers),
                ];
            }
            if ($userMealVouchers && in_array($id, $userMealVouchers)) {
                $gotMeals[$id] = [
                    'id' => $id,
                    'name' => $meal['name'][substr($locale, 0, 2)],
                    'endtime' => $endTime,
                ];
            }
        }
        uasort($futureMeals, fn($a, $b) => $a['endtime']->timestamp - $b['endtime']->timestamp);
        uasort($gotMeals, fn($a, $b) => $a['endtime']->timestamp - $b['endtime']->timestamp);

        return ['futureMeals' => array_filter(
            array_slice(
                $futureMeals,
                0,
                3
            ),
            function ($v) {
                return $v['hidden'] === false;
            }
        ),
        'gotMealVouchers' => $gotMeals,
        ];
    }

    public function view(): Response
    {
        $this->checkActive();

        /** @var User $user */
        $user = $this->auth->user();
        $crew = $this->userIsCrew();
        $getInfo = $this->getInfo($crew, $user->state->meals);

        return $this->response->withView(
            'pages/food-voucher.twig',
            [
                'meals' => $getInfo['futureMeals'],
                'emailFood' => $user->settings->email_food,
                'crew' => $crew,
                'userMealVouchers' => $getInfo['gotMealVouchers'],
                'eligibleVoucherCount' => UserVouchers::eligibleVoucherCount($user),
            ]
        );
    }

    /**
     * @throws ErrorException
     */
    public function send(Request $request): Response
    {
        $this->checkActive();
        /** @var User $user */
        $user = $this->auth->user();
        $crew = $this->userIsCrew();

        $postUrl = $this->foodVoucherApi['post_url'];
        $getInfo = $this->getInfo($crew, $user->state->meals);
        $meals = array_diff(array_keys($getInfo['futureMeals']), array_keys($getInfo['gotMealVouchers']));

        $data = $this->validate($request, [
            'meal_id' => 'required|in:' . implode(',', array_values($meals)),
        ]);

        $email = $user->settings->email_food ? $user->email : $this->foodVoucherApi['default_email'];


        $postData = [
            'email' => $email,
            'type' => $crew ? 'crew' : 'regular',
            'meal' => $data['meal_id'],
        ];

        try {
            $response = $this->guzzle->post(
                $postUrl,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getAuthToken(),
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($postData),
                ]
            );
        } catch (ConnectException | GuzzleException $e) {
            $this->log->error('Exception during food voucher api request', ['exception' => $e]);
            throw new ErrorException('user.food.request-error');
        }
        if ($response->getStatusCode() === 418) {
            cache()->forget('foodVoucherInfo');
            warning(__('user.food.no_food'));
            return $this->redirect->to(url('/food'));
        }
        if ($response->getStatusCode() !== 200) {
            throw new HttpNotFound('user.food.request-error');
        }
        $user->state->got_voucher += 1;
        $user->state->meals = array_merge($user->state->meals ?? [], [$data['meal_id']]);
        $user->state->save();

        $logHelper = [];
        if ($user->state->force_active) {
            $logHelper[] = 'fa';
        }
        if ($user->state->force_food) {
            $logHelper[] = 'ff';
        }

        $this->log->info(
            'Food Voucher {crew} generated. Got {got_voucher} vouchers.',
            [
                'got_voucher' => $user->state->got_voucher,
                'crew' => $crew ? '(' . join(', ', $logHelper) . ')' : '',
            ]
        );

        return $this->redirect->to($this->foodVoucherApi['claim_redirect_url']
            . '?qr=' . $response->getBody()->getContents() . '&meal=' . $data['meal_id']);
    }
}
