# Implementation Plan: Phase 3 - Guardian Management

## Task Summary
- **Story:** Minor Volunteer Support
- **Task:** Guardian Management - dashboard, linking, consent, and minor management
- **Type:** Backend + Views (Twig)
- **Estimated Effort:** L

## Prerequisites
- [x] Phase 1: Core data model migrations completed
- [x] Phase 2: MinorRestrictionService completed
- [ ] No external dependencies

## Files to Create

| File | Purpose | Est. Lines |
|------|---------|------------|
| `src/Services/GuardianService.php` | Business logic for guardian-minor relationships | 250 |
| `src/Controllers/GuardianController.php` | HTTP handlers for guardian pages | 300 |
| `resources/views/pages/guardian/dashboard.twig` | Guardian's view of linked minors | 150 |
| `resources/views/pages/guardian/link-minor.twig` | Form to link existing minor account | 120 |
| `resources/views/pages/guardian/register-minor.twig` | Form to register new minor account | 180 |
| `resources/views/pages/guardian/consent-form.twig` | Printable consent form template | 100 |
| `resources/views/pages/guardian/minor-profile.twig` | View/edit minor's profile | 200 |
| `tests/Unit/Services/GuardianServiceTest.php` | Unit tests for GuardianService | 400 |
| `tests/Unit/Controllers/GuardianControllerTest.php` | Controller tests | 350 |

## Files to Modify

| File | Changes | Est. Lines Added |
|------|---------|------------------|
| `config/routes.php` | Add /guardian route group | 25 |
| `resources/lang/en_US/default.po` | Add guardian-related translations | 50 |
| `resources/lang/de_DE/default.po` | Add guardian-related translations | 50 |

## Implementation Details

### 1. GuardianService

**File:** `src/Services/GuardianService.php`
**Pattern to follow:** `src/Services/MinorRestrictionService.php`

```php
class GuardianService
{
    public function __construct(
        protected MinorRestrictionService $minorService
    ) {}

    // === Guardian-Minor Relationship Methods ===
    public function linkGuardianToMinor(User $guardian, User $minor, array $options): UserGuardian
    public function unlinkGuardianFromMinor(User $guardian, User $minor): void
    public function getLinkedMinors(User $guardian): Collection
    public function getGuardians(User $minor): Collection
    public function getPrimaryGuardian(User $minor): ?User
    public function setPrimaryGuardian(User $guardian, User $minor): void
    public function canManageMinor(User $guardian, User $minor): bool

    // === Minor Registration Methods ===
    public function registerMinor(User $guardian, array $data): User
    public function updateMinorProfile(User $guardian, User $minor, array $data): User
    public function changeMinorCategory(User $guardian, User $minor, MinorCategory $category): void

    // === Link Request Methods ===
    public function createLinkRequest(User $minor, string $guardianEmail): LinkRequest
    public function acceptLinkRequest(User $guardian, LinkRequest $request): UserGuardian
    public function generateMinorLinkCode(User $minor): string
    public function linkByCode(User $guardian, string $code): UserGuardian

    // === Shift Management for Minors ===
    public function signUpMinorForShift(User $guardian, User $minor, Shift $shift, AngelType $angelType): ShiftEntry
    public function removeMinorFromShift(User $guardian, User $minor, ShiftEntry $entry): void
    public function getMinorShiftHistory(User $minor): Collection
    public function getMinorUpcomingShifts(User $minor): Collection

    // === Validation Methods ===
    public function validateGuardianEligibility(User $guardian): ValidationResult
    public function validateMinorRegistration(array $data): ValidationResult
}
```

**Key considerations:**
- Guardian must be adult (no minor_category_id)
- Primary guardian designation uses `is_primary` flag
- Delegated guardians can have time-limited validity
- Only primary guardian can remove other guardians

### 2. GuardianController

**File:** `src/Controllers/GuardianController.php`
**Pattern to follow:** `src/Controllers/SettingsController.php`

```php
class GuardianController extends BaseController
{
    protected array $permissions = ['user_guardian'];

    public function __construct(
        protected Authenticator $auth,
        protected GuardianService $guardianService,
        protected MinorRestrictionService $minorService,
        protected Redirector $redirect,
        protected Response $response
    ) {}

    // === Dashboard ===
    public function dashboard(): Response       // GET /guardian

    // === Link Minor ===
    public function linkMinor(): Response       // GET /guardian/link
    public function saveLinkMinor(): Response   // POST /guardian/link
    public function linkByCode(): Response      // POST /guardian/link/code

    // === Register Minor ===
    public function registerMinor(): Response   // GET /guardian/register
    public function saveRegisterMinor(): Response // POST /guardian/register

    // === Minor Profile ===
    public function viewMinor(Request $request): Response      // GET /guardian/minor/{id}
    public function editMinor(Request $request): Response      // GET /guardian/minor/{id}/edit
    public function saveMinor(Request $request): Response      // POST /guardian/minor/{id}/edit
    public function changeCategory(Request $request): Response // POST /guardian/minor/{id}/category

    // === Consent Form ===
    public function consentForm(Request $request): Response    // GET /guardian/minor/{id}/consent

    // === Shift Management ===
    public function minorShifts(Request $request): Response    // GET /guardian/minor/{id}/shifts
    public function signUpForShift(Request $request): Response // POST /guardian/minor/{id}/shifts
    public function removeFromShift(Request $request): Response // POST /guardian/minor/{id}/shifts/{entry}/remove

    // === Guardian Management ===
    public function addGuardian(Request $request): Response    // POST /guardian/minor/{id}/guardians
    public function removeGuardian(Request $request): Response // POST /guardian/minor/{id}/guardians/{guardian}/remove
    public function setPrimaryGuardian(Request $request): Response // POST /guardian/minor/{id}/guardians/{guardian}/primary
}
```

**Key considerations:**
- Validate guardian is adult before any action
- Validate guardian has permission to manage specific minor
- Use existing form validation patterns
- Proper CSRF protection on all forms

### 3. Route Configuration

**File:** `config/routes.php`

```php
// Guardian Management
$route->addGroup(
    '/guardian',
    function (RouteCollector $route): void {
        $route->get('', 'GuardianController@dashboard');

        // Link existing minor
        $route->get('/link', 'GuardianController@linkMinor');
        $route->post('/link', 'GuardianController@saveLinkMinor');
        $route->post('/link/code', 'GuardianController@linkByCode');

        // Register new minor
        $route->get('/register', 'GuardianController@registerMinor');
        $route->post('/register', 'GuardianController@saveRegisterMinor');

        // Minor management
        $route->addGroup(
            '/minor/{minor_id:\d+}',
            function (RouteCollector $route): void {
                $route->get('', 'GuardianController@viewMinor');
                $route->get('/edit', 'GuardianController@editMinor');
                $route->post('/edit', 'GuardianController@saveMinor');
                $route->post('/category', 'GuardianController@changeCategory');
                $route->get('/consent', 'GuardianController@consentForm');
                $route->get('/shifts', 'GuardianController@minorShifts');
                $route->post('/shifts', 'GuardianController@signUpForShift');
                $route->post('/shifts/{entry_id:\d+}/remove', 'GuardianController@removeFromShift');
                $route->post('/guardians', 'GuardianController@addGuardian');
                $route->post('/guardians/{guardian_id:\d+}/remove', 'GuardianController@removeGuardian');
                $route->post('/guardians/{guardian_id:\d+}/primary', 'GuardianController@setPrimaryGuardian');
            }
        );
    }
);
```

### 4. View Templates

#### 4a. Guardian Dashboard (`dashboard.twig`)

**File:** `resources/views/pages/guardian/dashboard.twig`
**Pattern to follow:** `resources/views/pages/settings/profile.twig`

```twig
{% extends 'layouts/app.twig' %}
{% import 'macros/base.twig' as m %}

{% block title %}{{ __('guardian.dashboard') }}{% endblock %}

{% block content %}
<div class="container">
    <h1>{{ __('guardian.dashboard') }}</h1>

    {# Action buttons #}
    <div class="mb-4">
        <a href="/guardian/register" class="btn btn-primary">
            {{ m.icon('plus') }} {{ __('guardian.register_minor') }}
        </a>
        <a href="/guardian/link" class="btn btn-outline-primary">
            {{ m.icon('link') }} {{ __('guardian.link_existing') }}
        </a>
    </div>

    {# Linked minors list #}
    {% if minors|length > 0 %}
        <div class="row g-4">
        {% for minor in minors %}
            {# Minor card with status, category, consent, upcoming shifts #}
        {% endfor %}
        </div>
    {% else %}
        {{ m.info(__('guardian.no_minors_yet')) }}
    {% endif %}
</div>
{% endblock %}
```

**Display for each minor:**
- Name and username
- Minor category badge (Junior Angel, Teen Angel, etc.)
- Consent status (Approved / Pending)
- Daily hours used/remaining
- Upcoming shifts count
- Quick actions: View, Edit, Manage Shifts

#### 4b. Link Minor Form (`link-minor.twig`)

Two options for linking:
1. Enter minor's username/email + send link request
2. Enter minor's link code (for self-registered minors)

#### 4c. Register Minor Form (`register-minor.twig`)

Form fields:
- Username (unique)
- First name, Last name (if enabled)
- Email (optional, for notifications)
- Minor category selection with descriptions
- Pronoun (if enabled)
- Relationship type (parent/legal_guardian/delegated)

#### 4d. Consent Form (`consent-form.twig`)

**Purpose:** Printable consent form template based on policy requirements

Content:
- Event name and dates
- Minor's name (from user record)
- Guardian's name
- Minor category and restrictions summary
- Consent statements (from policy Section 8)
- Signature lines for guardian
- Verification section (for Heaven staff at arrival)

**Key considerations:**
- Print-friendly CSS (no navigation, clean layout)
- All required consent elements from policy
- QR code linking back to minor's profile (for Heaven staff)

#### 4e. Minor Profile (`minor-profile.twig`)

View mode:
- Profile information
- Minor category and restrictions
- Consent status and history
- Linked guardians list
- Shift history and upcoming shifts
- Daily hours tracking

Edit mode:
- Editable profile fields
- Category change (with confirmation)
- Guardian management (add/remove secondary)

### 5. Permission Configuration

Add new permission `user_guardian` to be granted to all authenticated users (adults only can access guardian features - controller validates).

**File:** May need to add to default permissions or create migration for permission.

## Test Plan

### Unit Tests: GuardianServiceTest

| Test Case | Description |
|-----------|-------------|
| testLinkGuardianToMinor | Link adult to minor creates relationship |
| testLinkGuardianToMinorFailsIfGuardianIsMinor | Guardian must be adult |
| testLinkGuardianToMinorFailsIfAlreadyLinked | Cannot duplicate relationship |
| testUnlinkGuardianFromMinor | Remove relationship |
| testUnlinkPrimaryGuardianFails | Cannot remove only primary guardian |
| testGetLinkedMinors | Returns all linked minors for guardian |
| testGetGuardians | Returns all guardians for minor |
| testSetPrimaryGuardian | Changes primary guardian designation |
| testCanManageMinor | Returns true only for linked guardians |
| testCanManageMinorWithExpiredDelegation | Returns false for expired delegations |
| testRegisterMinor | Creates minor account with guardian link |
| testRegisterMinorValidatesCategory | Category must exist and be active |
| testUpdateMinorProfile | Updates allowed fields |
| testChangeMinorCategory | Changes category with validation |
| testSignUpMinorForShift | Uses MinorRestrictionService validation |
| testSignUpMinorForShiftFailsIfRestricted | Enforces category restrictions |
| testSignUpMinorForShiftWithoutConsent | Fails if consent not approved |
| testRemoveMinorFromShift | Removes shift entry |
| testGetMinorShiftHistory | Returns past shifts |
| testGetMinorUpcomingShifts | Returns future shifts |
| testGenerateLinkCode | Creates unique code for minor |
| testLinkByCode | Links guardian using valid code |
| testLinkByCodeExpired | Fails with expired code |
| testValidateGuardianEligibility | Adults only |
| testDelegatedGuardianValidityPeriod | Time-limited access |

### Controller Tests: GuardianControllerTest

| Test Case | Description |
|-----------|-------------|
| testDashboardRequiresAuth | Redirect to login if not authenticated |
| testDashboardShowsLinkedMinors | Displays minors with correct info |
| testLinkMinorForm | Form renders correctly |
| testSaveLinkMinorValidation | Validates input |
| testRegisterMinorForm | Form shows category options |
| testSaveRegisterMinor | Creates minor with link |
| testViewMinorRequiresOwnership | Only linked guardian can view |
| testEditMinorForm | Pre-populates form |
| testSaveMinorValidation | Validates all fields |
| testConsentFormPrintable | Renders print-friendly layout |
| testMinorShiftsShowsRestrictions | Displays hours remaining |
| testSignUpForShiftValidation | Enforces all restrictions |
| testRemoveFromShift | Removes entry correctly |
| testAddGuardian | Creates secondary guardian |
| testRemoveGuardianRequiresPrimary | Only primary can remove |
| testSetPrimaryGuardian | Updates is_primary flags |

## Code Quality Checklist
- [ ] Functions < 25 lines (target), < 50 (max)
- [ ] File < 300 lines (service) / 500 lines (controller)
- [ ] Type annotations on all functions
- [ ] Docstrings for public methods
- [ ] No TODO/FIXME comments
- [ ] Follows existing naming conventions
- [ ] Uses dependency injection
- [ ] Proper error handling with user-friendly messages

## Implementation Order

1. **GuardianService** - Core business logic first
   - Relationship management methods
   - Validation methods
   - Unit tests

2. **GuardianController** - HTTP layer
   - Dashboard endpoint
   - Link/Register endpoints
   - Minor management endpoints
   - Controller tests

3. **Routes Configuration** - Wire up endpoints

4. **View Templates** - UI layer
   - Dashboard view
   - Forms (link, register, edit)
   - Consent form (printable)
   - Minor profile view

5. **Translations** - i18n support
   - English strings
   - German strings

## Key Design Decisions

### Guardian Validation
- Must be adult (no minor_category_id)
- Cannot be the minor themselves
- For delegated guardians, check validity period

### Minor Registration by Guardian
- Guardian automatically becomes primary
- relationship_type defaults to 'parent'
- can_manage_account defaults to true
- Minor account created in "pending consent" state

### Consent Flow
1. Guardian registers or links minor
2. System generates printable consent form
3. Guardian prints, completes, brings to event
4. Heaven staff verifies paper consent at arrival
5. Staff marks minor as "arrived" (sets consent_approved_by_user_id)
6. Minor can now sign up for shifts

### Shift Signup by Guardian
- Guardian can sign up minors they manage
- All MinorRestrictionService validations apply
- Guardian counts as supervisor for own minors
- Non-counting flag set based on minor category

## Dependencies on Existing Code

- `MinorRestrictionService` - For all restriction validation
- `UserGuardian` model - For relationship storage
- `MinorCategory` model - For category selection
- `User` factory - For creating minor accounts
- `ShiftEntry` model - For shift signups
- Form validation patterns from `BaseController`
- Twig macros from `resources/views/macros/`

## Notes

- No school holiday logic needed - guardian selects appropriate category
- Paper consent forms stored offline per Datensparsamkeit
- System only records who approved consent and when
- QR code on consent form helps Heaven staff verify quickly
