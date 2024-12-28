<?php

declare(strict_types=1);

namespace Engelsystem\Http\SessionHandlers;

use Engelsystem\Database\Database;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\Session;

class DatabaseHandler extends AbstractHandler
{
    public function __construct(protected Database $database)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $id): string
    {
        $session = Session::whereId($id)->first();

        return $session ? $session->payload : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $id, string $data): bool
    {
        $session = Session::findOrNew($id);
        $session->id = $id;
        $session->payload = $data;
        $session->last_activity = Carbon::now();
        $session->user_id = auth()->user()?->id ?? null;
        $session->save();

        // The save return can't be used directly as it won't change if the second call is in the same second
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $id): bool
    {
        Session::whereId($id)->delete();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $max_lifetime): int|false
    {
        $sessionDays = config('session')['lifetime'];
        $deleteBefore = Carbon::now()->subDays($sessionDays);

        return Session::where('last_activity', '<', $deleteBefore)
            ->delete();
    }
}
