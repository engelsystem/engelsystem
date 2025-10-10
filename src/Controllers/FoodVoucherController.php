<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Helpers\UserVouchers;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use ErrorException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class FoodVoucherController extends BaseController
{
    use HasUserNotifications;

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
    }

    private function checkActive(): void
    {
        if (!$this->config->get('enable_voucher') || !$this->config->get('enable_force_food')) {
            throw new HttpNotFound();
        }
    }

    private function checkConfig(): void
    {
        $conf = $this->config->get('food_voucher_api');
        if (!(
            $conf
            && $conf['info_url']
            && $conf['auth_token']
            && $conf['post_url']
        )) {
            throw new HttpNotFound();
        }
    }

    private function getAuthToken(): string
    {
        return $this->config->get('food_voucher_api')['auth_token'];
    }

    private function userIsForced(): bool
    {
        $user = $this->auth->user();
        return $user->state->force_active && $this->config->get('enable_force_active')
            || $user->state->force_food && $this->config->get('enable_force_food');
    }

    /**
     * @throws ErrorException
     */
    private function getInfo(bool $crew, array $userMealVouchers = null): array
    {
        $this->checkConfig();
        $infoUrl = (string) $this->config->get('food_voucher_api')['info_url'];
        $this->log->info($infoUrl);
        $this->log->info($this->getAuthToken());
        try {
            $response = $this->guzzle->get(
                $infoUrl,
                ['headers' => [
                    'Authorization' => 'Bearer ' . $this->getAuthToken(),
                    'Content-Type' => 'application/json'
                    ]
                ]
            );
        } catch (ConnectException | GuzzleException $e) {
            $this->log->error('Exception during food voucher api request', ['exception' => $e]);
            throw new ErrorException('user.food.request-error');
        }
        $data = json_decode($response->getBody()->getContents(), true);
        $now = Carbon::now();
        $locale = $this->translator->getLocale();
        $futureMeals = [];

        foreach ($data as $id => $meal) {
            $endTime = Carbon::parse($meal['datetime']['date'] . ' ' . $meal['datetime']['end']);
            if ($now < $endTime) {
                $available = false;
                if ($userMealVouchers && !in_array($id, $userMealVouchers)) {
                    $available = $crew
                        ? $meal['availability']['crew']
                        : $meal['availability']['regular'];
                }

                $futureMeals[$id] = [
                    'id' => $id,
                    'name' => $locale === 'de_DE' ? $meal['name']['de'] : $meal['name']['en'],
                    'endtime' => $endTime,
                    'available' => $available,
                ];
            }
        }

        return array_slice($futureMeals, 0, 3);
    }

    public function view(): Response
    {
        $this->checkActive();

        /** @var User $user */
        $user = $this->auth->user();
        $crew = $this->userIsForced();

        return $this->response->withView(
            'pages/food-voucher.twig',
            [
                'meals' => $this->getInfo($crew, $user->state->meal_vouchers),
                'email_food' => $user->settings->email_food,
                'gotVoucher' => $user->state->got_voucher ?? 0,
                'crew' => $crew,
                'eligibleVoucherCount' => UserVouchers::eligibleVoucherCount($user),
            ]
        );
    }

    public function send(Request $request): Response
    {
        $this->checkActive();
        /** @var User $user */
        $user = $this->auth->user();
        $postUrl = (string) $this->config->get('food_voucher_api')['post_url'];
        $crew = $this->userIsForced();
        $meals = $this->getInfo($crew, $user->state->meal_vouchers);

        $data = $this->validate($request, [
            'meal_id' => 'required|in:' . implode(',', array_keys($meals)),
        ]);

        $email = $user->settings->email_food ? $user->email : $this->config->get('food_voucher_api')['default_email'];


        $postData = [
            'email' => $email,
            'type' => $crew ? 'crew' : 'regular',
            'meal'=> $data['meal_id'],
        ];


        try {
            $response = $this->guzzle->post(
                $postUrl,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getAuthToken(),
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode($postData)
                ]
            );
        } catch (ConnectException | GuzzleException $e) {
            $this->log->error('Exception during food voucher api request', ['exception' => $e]);
            throw new ErrorException('user.food.request-error');
        }
//        dd($response->getStatusCode(), $response);
        if ($response->getStatusCode() !== 200) {
            throw new HttpForbidden();
        }
        $user->state->got_voucher += 1;
        $user->state->meal_vouchers = array_merge($user->state->meal_vouchers ?? [], [$data['meal_id']]);
        $user->state->save();

        $this->log->info(
            'Food Voucher generated. Got {got_voucher} vouchers.',
            [
                'got_voucher' => $user->state->got_voucher,
            ]
        );

        return $this->redirect->to($this->config->get('food_voucher_api')['redirect_url']
            .'?qr=' . $response->getBody()->getContents() . '&meal=' . $data['meal_id']);
    }
}
