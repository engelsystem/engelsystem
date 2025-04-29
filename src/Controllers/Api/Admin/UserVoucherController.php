<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Admin;

use Engelsystem\Controllers\Api\ApiController;
use Engelsystem\Controllers\Api\UsesAuth;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;

class UserVoucherController extends ApiController {
    use UsesAuth;

    public array $permissions = [
        'api',
        'voucher.edit',
    ];

    public function __construct(Response $response) {
        parent::__construct($response);
        $this->checkActive();
    }

    /**
     * Set the voucher count of a user.
     */
    public function update(Request $request): Response {
        $userId     = $request->getAttribute('user_id');
        $targetUser = $this->getUser($userId);

        if(!$targetUser) {
            return $this->response
                ->withStatus(404)
                ->withContent(json_encode(['message' => 'User not found']));
        }

        $state = $targetUser->state;

        $data = $this->validate($request, [
            'got_voucher' => ['required', 'int', 'min:0'],
        ]);

        $state->update(['got_voucher' => $data['got_voucher']]);

        return $this->response
            ->withContent(json_encode(['message' => 'User state updated successfully']));
    }

    /**
     * Increment (or decrement) the voucher count of a user by a given amount.
     */
    public function increment(Request $request): Response {
        $userId     = $request->getAttribute('user_id');
        $targetUser = $this->getUser($userId);

        if(!$targetUser) {
            return $this->response
                ->withStatus(404)
                ->withContent(json_encode(['message' => 'User not found']));
        }

        $state = $targetUser->state;

        $data = $this->validate($request, [
            'amount' => ['required', 'int'],
        ]);

        $amount    = (int)$data['amount'];
        $newAmount = $state->got_voucher + $amount;

        if($newAmount < 0) {
            return $this->response
                ->withStatus(400)
                ->withContent(json_encode(['message' => 'User voucher count cannot be negative']));
        }

        $state->update(['got_voucher' => $newAmount]);

        return $this->response
            ->withContent(json_encode([
                                          'message'     => 'User voucher count updated',
                                          'got_voucher' => $state->got_voucher,
                                      ]));
    }

    /**
     * @return void
     * @todo duplicate code from UserVoucherController (frontend)
     */
    private function checkActive(): void {
        if(!config('enable_voucher')) {
            throw new HttpNotFound();
        }
    }
}
