<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Events\DataEvent;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Shifts\Shift;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface;

class ShiftsController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'admin_shifts',
    ];

    public function __construct(
        protected LoggerInterface $log,
        protected Shift $shift,
        protected Redirector $redirect,
        protected Response $response,
        protected EventDispatcher $events,
    ) {
    }

    public function history(): Response
    {
        $shifts = $this->shift
            ->select()
            ->selectRaw('MIN(start) AS start')
            ->selectRaw('MAX(end) AS end')
            ->selectRaw('COUNT(*) AS count')
            ->selectRaw('MIN(created_at) AS created_at')
            ->with(['schedule', 'createdBy'])
            ->whereNotNull('transaction_id')
            ->groupBy('transaction_id')
            ->orderByDesc('created_at')
            ->get();
        return $this->response->withView('admin/shifts/history', ['shifts' => $shifts]);
    }

    public function deleteTransaction(Request $request): Response
    {
        $transactionId = $request->postData('transaction_id');

        /** @var Shift[]|Collection $shifts */
        $shifts = $this->shift
            ->where('transaction_id', $transactionId)
            ->with('shiftType')
            ->get();

        $info = 'Deleting {count} shifts with transaction ID: {id}' . PHP_EOL;
        foreach ($shifts as $shift) {
            $info .= '  ' . $shift->title . ' / ' . $shift->shiftType->name
                . ' from ' . $shift->start->format('Y-m-d H:i')
                . ' to ' . $shift->end->format('Y-m-d H:i')
                . PHP_EOL;
        }
        $this->log->info($info, ['count' => $shifts->count(), 'id' => $transactionId]);

        $this->events->dispatch(new DataEvent('shifts.deleting', $shifts));
        $shifts->toQuery()->delete();

        $this->addNotification('shifts.history.delete.success');

        return $this->redirect->back();
    }
}
