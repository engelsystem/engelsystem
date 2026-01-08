<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Carbon\Carbon;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\User\User;
use Engelsystem\Services\MinorRestrictionService;
use Illuminate\Support\Collection;

class MinorManagementController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'admin_arrive',
    ];

    public function __construct(
        protected Response $response,
        protected Authenticator $auth,
        protected MinorRestrictionService $minorService,
        protected Redirector $redirect,
    ) {
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $categoryFilter = $request->input('category');
        $consentFilter = $request->input('consent');

        // Get all minors
        $query = User::with(['personalData', 'minorCategory', 'guardians.guardian.personalData'])
            ->whereNotNull('minor_category_id');

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('personalData', function ($pq) use ($search): void {
                        $pq->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Apply category filter
        if (!empty($categoryFilter)) {
            $query->where('minor_category_id', $categoryFilter);
        }

        // Apply consent filter
        if ($consentFilter === 'approved') {
            $query->whereNotNull('consent_approved_by_user_id');
        } elseif ($consentFilter === 'pending') {
            $query->whereNull('consent_approved_by_user_id');
        }

        $minors = $query->orderBy('name')->get();

        // Calculate daily hours for today
        $today = Carbon::today();
        $minorsData = $minors->map(function (User $minor) use ($today): array {
            $hoursUsed = $this->minorService->getDailyHoursUsed($minor, $today);
            $restrictions = $this->minorService->getRestrictions($minor);

            return [
                'user' => $minor,
                'hoursUsed' => $hoursUsed,
                'maxHours' => $restrictions->maxHoursPerDay,
                'hoursPercent' => $restrictions->maxHoursPerDay
                    ? min(100, round(($hoursUsed / $restrictions->maxHoursPerDay) * 100))
                    : 0,
            ];
        });

        // Get supervision gaps (shifts with minors but no supervisor)
        $supervisionGaps = $this->minorService->getSupervisionGaps();

        // Get category statistics
        $categoryStats = $this->getCategoryStatistics();

        // Get consent statistics
        $consentStats = [
            'approved' => User::whereNotNull('minor_category_id')
                ->whereNotNull('consent_approved_by_user_id')
                ->count(),
            'pending' => User::whereNotNull('minor_category_id')
                ->whereNull('consent_approved_by_user_id')
                ->count(),
        ];

        // Get categories for filter dropdown
        $categories = MinorCategory::where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->mapWithKeys(fn(MinorCategory $cat): array => [$cat->id => $cat->name]);

        return $this->response->withView(
            'admin/minors/index.twig',
            [
                'minors' => $minorsData,
                'search' => $search,
                'categories' => $categories,
                'category' => $categoryFilter,
                'consent' => $consentFilter,
                'supervisionGaps' => $supervisionGaps,
                'categoryStats' => $categoryStats,
                'consentStats' => $consentStats,
                'today' => $today,
            ]
        );
    }

    /**
     * Get statistics by minor category.
     *
     * @return Collection<string, int>
     */
    protected function getCategoryStatistics(): Collection
    {
        return MinorCategory::withCount(['users' => function ($query): void {
            $query->whereNotNull('minor_category_id');
        }])
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->mapWithKeys(fn(MinorCategory $cat): array => [$cat->name => $cat->users_count ?? 0]);
    }

    /**
     * Approve a minor's consent (marks paper consent form as verified).
     */
    public function approveConsent(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id');
        $minor = User::find($userId);

        if (!$minor || !$minor->isMinor()) {
            $this->addNotification('minors.not_found', NotificationType::ERROR);
            return $this->redirect->to('/admin/minors');
        }

        if ($minor->hasConsentApproved()) {
            $this->addNotification('minors.consent.already_approved', NotificationType::WARNING);
            return $this->redirect->to('/admin/minors');
        }

        $approver = $this->auth->user();
        $minor->consent_approved_by_user_id = $approver->id;
        $minor->consent_approved_at = Carbon::now();
        $minor->save();

        $this->addNotification('minors.consent.approved_success');
        return $this->redirect->to('/admin/minors');
    }

    /**
     * Revoke a minor's consent approval.
     */
    public function revokeConsent(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id');
        $minor = User::find($userId);

        if (!$minor || !$minor->isMinor()) {
            $this->addNotification('minors.not_found', NotificationType::ERROR);
            return $this->redirect->to('/admin/minors');
        }

        if (!$minor->hasConsentApproved()) {
            $this->addNotification('minors.consent.not_approved', NotificationType::WARNING);
            return $this->redirect->to('/admin/minors');
        }

        $minor->consent_approved_by_user_id = null;
        $minor->consent_approved_at = null;
        $minor->save();

        $this->addNotification('minors.consent.revoked_success');
        return $this->redirect->to('/admin/minors');
    }
}
