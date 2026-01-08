<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Carbon\Carbon;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\User\User;
use Engelsystem\Services\GuardianService;
use Engelsystem\Services\MinorRestrictionService;
use InvalidArgumentException;

class GuardianController extends BaseController
{
    use HasUserNotifications;

    /** @var string[] */
    protected array $permissions = [
        'user_guardian',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected GuardianService $guardianService,
        protected MinorRestrictionService $minorService,
        protected Redirector $redirect,
        protected Response $response
    ) {
    }

    /**
     * Guardian dashboard showing linked minors
     */
    public function dashboard(): Response
    {
        $guardian = $this->auth->user();

        if (!$this->guardianService->isEligibleGuardian($guardian)) {
            $this->addNotification('guardian.not_eligible', NotificationType::ERROR);
            return $this->redirect->to('/');
        }

        $minors = $this->guardianService->getLinkedMinors($guardian);

        // Enrich minor data with additional info
        $minorData = $minors->map(function (User $minor) use ($guardian) {
            $category = $this->minorService->getCategory($minor);
            $restrictions = $this->minorService->getRestrictions($minor);
            $upcomingShifts = $this->guardianService->getMinorUpcomingShifts($minor);
            $link = $this->guardianService->getGuardianLink($guardian, $minor);

            return [
                'user'            => $minor,
                'category'        => $category,
                'restrictions'    => $restrictions,
                'consentApproved' => $minor->hasConsentApproved(),
                'upcomingShifts'  => $upcomingShifts,
                'isPrimary'       => $link?->is_primary ?? false,
                'dailyHoursUsed'  => $this->minorService->getDailyHoursUsed($minor, Carbon::now()),
            ];
        });

        return $this->response->withView('pages/guardian/dashboard.twig', [
            'minors' => $minorData,
        ]);
    }

    /**
     * Show form to link existing minor account
     */
    public function linkMinor(): Response
    {
        $guardian = $this->auth->user();

        if (!$this->guardianService->isEligibleGuardian($guardian)) {
            return $this->redirect->to('/');
        }

        return $this->response->withView('pages/guardian/link-minor.twig');
    }

    /**
     * Process linking of existing minor account
     */
    public function saveLinkMinor(Request $request): Response
    {
        $guardian = $this->auth->user();

        if (!$this->guardianService->isEligibleGuardian($guardian)) {
            return $this->redirect->to('/');
        }

        $data = $this->validate($request, [
            'minor_identifier' => 'required',
            'relationship_type' => 'required',
        ]);

        // Find minor by username or email
        $minor = User::where('name', $data['minor_identifier'])
            ->orWhere('email', $data['minor_identifier'])
            ->first();

        if (!$minor) {
            $this->addNotification('guardian.minor_not_found', NotificationType::ERROR);
            return $this->redirect->to('/guardian/link');
        }

        if (!$minor->isMinor()) {
            $this->addNotification('guardian.user_not_minor', NotificationType::ERROR);
            return $this->redirect->to('/guardian/link');
        }

        try {
            $this->guardianService->linkGuardianToMinor($guardian, $minor, [
                'relationship_type' => $data['relationship_type'],
            ]);
            $this->addNotification('guardian.link_successful');
        } catch (InvalidArgumentException $e) {
            $this->addNotification($e->getMessage(), NotificationType::ERROR);
            return $this->redirect->to('/guardian/link');
        }

        return $this->redirect->to('/guardian');
    }

    /**
     * Show form to register new minor account
     */
    public function registerMinor(): Response
    {
        $guardian = $this->auth->user();

        if (!$this->guardianService->isEligibleGuardian($guardian)) {
            return $this->redirect->to('/');
        }

        $categories = MinorCategory::active()->minorOnly()->get();

        return $this->response->withView('pages/guardian/register-minor.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * Process registration of new minor account
     */
    public function saveRegisterMinor(Request $request): Response
    {
        $guardian = $this->auth->user();

        if (!$this->guardianService->isEligibleGuardian($guardian)) {
            return $this->redirect->to('/');
        }

        $data = $this->validate($request, [
            'name'              => 'required|min:2|max:24',
            'email'             => 'optional|email',
            'password'          => 'required|min:8',
            'minor_category_id' => 'required|int',
            'first_name'        => 'optional|max:64',
            'last_name'         => 'optional|max:64',
            'pronoun'           => 'optional|max:15',
        ]);

        // Check if username already exists
        if (User::whereName($data['name'])->exists()) {
            $this->addNotification('registration.nick.taken', NotificationType::ERROR);
            return $this->redirect->to('/guardian/register');
        }

        try {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $minor = $this->guardianService->registerMinor($guardian, $data);
            $this->addNotification('guardian.registration_successful');
            return $this->redirect->to('/guardian/minor/' . $minor->id);
        } catch (InvalidArgumentException $e) {
            $this->addNotification($e->getMessage(), NotificationType::ERROR);
            return $this->redirect->to('/guardian/register');
        }
    }

    /**
     * View a minor's profile
     */
    public function viewMinor(Request $request): Response
    {
        $guardian = $this->auth->user();
        $minor = $this->getMinorFromRequest($request, $guardian);

        if (!$minor) {
            return $this->redirect->to('/guardian');
        }

        $category = $this->minorService->getCategory($minor);
        $restrictions = $this->minorService->getRestrictions($minor);
        $guardianLinks = $this->guardianService->getGuardianLinks($minor);
        $upcomingShifts = $this->guardianService->getMinorUpcomingShifts($minor);
        $shiftHistory = $this->guardianService->getMinorShiftHistory($minor);
        $isPrimary = $this->guardianService->isPrimaryGuardian($guardian, $minor);

        return $this->response->withView('pages/guardian/minor-profile.twig', [
            'minor'           => $minor,
            'category'        => $category,
            'restrictions'    => $restrictions,
            'guardianLinks'   => $guardianLinks,
            'upcomingShifts'  => $upcomingShifts,
            'shiftHistory'    => $shiftHistory,
            'consentApproved' => $minor->hasConsentApproved(),
            'isPrimary'       => $isPrimary,
            'dailyHoursUsed'  => $this->minorService->getDailyHoursUsed($minor, Carbon::now()),
            'dailyHoursMax'   => $restrictions->maxHoursPerDay,
        ]);
    }

    /**
     * Show edit form for a minor
     */
    public function editMinor(Request $request): Response
    {
        $guardian = $this->auth->user();
        $minor = $this->getMinorFromRequest($request, $guardian);

        if (!$minor) {
            return $this->redirect->to('/guardian');
        }

        $categories = MinorCategory::active()->minorOnly()->get();

        return $this->response->withView('pages/guardian/edit-minor.twig', [
            'minor'      => $minor,
            'categories' => $categories,
        ]);
    }

    /**
     * Save changes to a minor's profile
     */
    public function saveMinor(Request $request): Response
    {
        $guardian = $this->auth->user();
        $minor = $this->getMinorFromRequest($request, $guardian);

        if (!$minor) {
            return $this->redirect->to('/guardian');
        }

        $data = $this->validate($request, [
            'email'      => 'optional|email',
            'first_name' => 'optional|max:64',
            'last_name'  => 'optional|max:64',
            'pronoun'    => 'optional|max:15',
        ]);

        try {
            $this->guardianService->updateMinorProfile($guardian, $minor, $data);
            $this->addNotification('guardian.profile_updated');
        } catch (InvalidArgumentException $e) {
            $this->addNotification($e->getMessage(), NotificationType::ERROR);
        }

        return $this->redirect->to('/guardian/minor/' . $minor->id);
    }

    /**
     * Change a minor's category
     */
    public function changeCategory(Request $request): Response
    {
        $guardian = $this->auth->user();
        $minor = $this->getMinorFromRequest($request, $guardian);

        if (!$minor) {
            return $this->redirect->to('/guardian');
        }

        $data = $this->validate($request, [
            'minor_category_id' => 'required|int',
        ]);

        $category = MinorCategory::find($data['minor_category_id']);
        if (!$category) {
            $this->addNotification('guardian.invalid_category', NotificationType::ERROR);
            return $this->redirect->to('/guardian/minor/' . $minor->id);
        }

        try {
            $this->guardianService->changeMinorCategory($guardian, $minor, $category);
            $this->addNotification('guardian.category_changed');
        } catch (InvalidArgumentException $e) {
            $this->addNotification($e->getMessage(), NotificationType::ERROR);
        }

        return $this->redirect->to('/guardian/minor/' . $minor->id);
    }

    /**
     * Show printable consent form
     */
    public function consentForm(Request $request): Response
    {
        $guardian = $this->auth->user();
        $minor = $this->getMinorFromRequest($request, $guardian);

        if (!$minor) {
            return $this->redirect->to('/guardian');
        }

        $category = $this->minorService->getCategory($minor);
        $restrictions = $this->minorService->getRestrictions($minor);

        return $this->response->withView('pages/guardian/consent-form.twig', [
            'minor'        => $minor,
            'guardian'     => $guardian,
            'category'     => $category,
            'restrictions' => $restrictions,
        ]);
    }

    /**
     * Show minor's shifts
     */
    public function minorShifts(Request $request): Response
    {
        $guardian = $this->auth->user();
        $minor = $this->getMinorFromRequest($request, $guardian);

        if (!$minor) {
            return $this->redirect->to('/guardian');
        }

        $restrictions = $this->minorService->getRestrictions($minor);
        $upcomingShifts = $this->guardianService->getMinorUpcomingShifts($minor);
        $shiftHistory = $this->guardianService->getMinorShiftHistory($minor);

        return $this->response->withView('pages/guardian/minor-shifts.twig', [
            'minor'          => $minor,
            'restrictions'   => $restrictions,
            'upcomingShifts' => $upcomingShifts,
            'shiftHistory'   => $shiftHistory,
            'dailyHoursUsed' => $this->minorService->getDailyHoursUsed($minor, Carbon::now()),
            'dailyHoursMax'  => $restrictions->maxHoursPerDay,
        ]);
    }

    /**
     * Add a guardian to a minor (primary guardian only)
     */
    public function addGuardian(Request $request): Response
    {
        $primaryGuardian = $this->auth->user();
        $minor = $this->getMinorFromRequest($request, $primaryGuardian);

        if (!$minor) {
            return $this->redirect->to('/guardian');
        }

        $data = $this->validate($request, [
            'guardian_identifier' => 'required',
            'relationship_type'   => 'required',
            'can_manage_account'  => 'optional|bool',
        ]);

        $newGuardian = User::where('name', $data['guardian_identifier'])
            ->orWhere('email', $data['guardian_identifier'])
            ->first();

        if (!$newGuardian) {
            $this->addNotification('guardian.guardian_not_found', NotificationType::ERROR);
            return $this->redirect->to('/guardian/minor/' . $minor->id);
        }

        try {
            $this->guardianService->addSecondaryGuardian($primaryGuardian, $minor, $newGuardian, [
                'relationship_type'  => $data['relationship_type'],
                'can_manage_account' => $data['can_manage_account'] ?? true,
            ]);
            $this->addNotification('guardian.guardian_added');
        } catch (InvalidArgumentException $e) {
            $this->addNotification($e->getMessage(), NotificationType::ERROR);
        }

        return $this->redirect->to('/guardian/minor/' . $minor->id);
    }

    /**
     * Remove a guardian from a minor (primary guardian only)
     */
    public function removeGuardian(Request $request): Response
    {
        $primaryGuardian = $this->auth->user();
        $minor = $this->getMinorFromRequest($request, $primaryGuardian);

        if (!$minor) {
            return $this->redirect->to('/guardian');
        }

        $guardianId = (int) $request->getAttribute('guardian_id');
        $guardianToRemove = User::find($guardianId);

        if (!$guardianToRemove) {
            $this->addNotification('guardian.guardian_not_found', NotificationType::ERROR);
            return $this->redirect->to('/guardian/minor/' . $minor->id);
        }

        try {
            $this->guardianService->removeSecondaryGuardian($primaryGuardian, $minor, $guardianToRemove);
            $this->addNotification('guardian.guardian_removed');
        } catch (InvalidArgumentException $e) {
            $this->addNotification($e->getMessage(), NotificationType::ERROR);
        }

        return $this->redirect->to('/guardian/minor/' . $minor->id);
    }

    /**
     * Set a guardian as primary (primary guardian only)
     */
    public function setPrimaryGuardian(Request $request): Response
    {
        $currentPrimary = $this->auth->user();
        $minor = $this->getMinorFromRequest($request, $currentPrimary);

        if (!$minor) {
            return $this->redirect->to('/guardian');
        }

        if (!$this->guardianService->isPrimaryGuardian($currentPrimary, $minor)) {
            $this->addNotification('guardian.not_primary', NotificationType::ERROR);
            return $this->redirect->to('/guardian/minor/' . $minor->id);
        }

        $guardianId = (int) $request->getAttribute('guardian_id');
        $newPrimary = User::find($guardianId);

        if (!$newPrimary) {
            $this->addNotification('guardian.guardian_not_found', NotificationType::ERROR);
            return $this->redirect->to('/guardian/minor/' . $minor->id);
        }

        try {
            $this->guardianService->setPrimaryGuardian($newPrimary, $minor);
            $this->addNotification('guardian.primary_changed');
        } catch (InvalidArgumentException $e) {
            $this->addNotification($e->getMessage(), NotificationType::ERROR);
        }

        return $this->redirect->to('/guardian/minor/' . $minor->id);
    }

    /**
     * Get minor from request and validate guardian access
     */
    protected function getMinorFromRequest(Request $request, User $guardian): ?User
    {
        $minorId = (int) $request->getAttribute('minor_id');
        $minor = User::find($minorId);

        if (!$minor) {
            $this->addNotification('guardian.minor_not_found', NotificationType::ERROR);
            return null;
        }

        if (!$this->guardianService->canManageMinor($guardian, $minor)) {
            $this->addNotification('guardian.no_access', NotificationType::ERROR);
            return null;
        }

        return $minor;
    }
}
