# Phase 4: Registration Flow Updates - Implementation Plan

## Overview

Add minor category selection to the user registration flow, enabling self-registration for minors (13+) with subsequent guardian linking.

## Scope

Per the implementation plan (doc/legal/minor-volunteer-implementation-plan.md):
- Add minor category selection to registration form
- For self-managed minors: require guardian linking post-registration
- Category descriptions explain applicable restrictions

## Files to Modify

| File | Changes |
|------|---------|
| `src/Controllers/RegistrationController.php` | Add MinorCategory to view data, redirect minors to guardian linking after registration |
| `src/Factories/User.php` | Validate and save `minor_category_id` |
| `resources/views/pages/registration.twig` | Add minor category selection UI |

## Files to Create

| File | Purpose |
|------|---------|
| `resources/views/pages/register/link-guardian.twig` | Post-registration guardian linking page for minors |
| `tests/Unit/Controllers/RegistrationControllerMinorTest.php` | Test minor registration flow |

## Implementation Details

### 1. RegistrationController Changes

```php
// Add import
use Engelsystem\Models\MinorCategory;

// In renderSignUpPage(), add to view data:
'minorCategories' => MinorCategory::active()
    ->where('can_self_signup', true)
    ->orderBy('display_order')
    ->get(),
'isMinorRegistrationEnabled' => $this->config->get('enable_minor_registration', true),

// In save(), after successful registration:
if ($user->isMinor()) {
    $this->session->set('pending_minor_user_id', $user->id);
    $this->addNotification('registration.minor.link_guardian', NotificationType::INFORMATION);
    return $this->redirect->to('/register/link-guardian');
}
```

### 2. User Factory Changes

```php
// In validateUser(), add:
$isMinorRegistrationEnabled = $this->config->get('enable_minor_registration', true);
if ($isMinorRegistrationEnabled) {
    $validationRules['minor_category_id'] = 'optional|int';
}

// In createUser(), modify user creation:
$user = new EngelsystemUser([
    'name'              => $data['username'],
    'password'          => '',
    'email'             => $data['email'],
    'api_key'           => '',
    'last_login_at'     => null,
    'minor_category_id' => $data['minor_category_id'] ?? null,
]);
```

### 3. Registration View Changes

Add a new section before "Event Data" for minor category selection:

```twig
{% if isMinorRegistrationEnabled and minorCategories|length > 0 %}
<div class="mb-5">
    <h2>{{ __('registration.age_category') }}</h2>
    <p class="text-muted">{{ __('registration.age_category_info') }}</p>

    <div class="row">
        <div class="col-md-8">
            {{ f.select(
                'minor_category_id',
                __('registration.minor_category'),
                minorCategoryOptions,
                {
                    'default_option': __('registration.i_am_adult'),
                    'selected': f.formData('minor_category_id', ''),
                    'info': __('registration.minor_category_hint'),
                }
            ) }}
        </div>
    </div>

    {# Show restriction info for each category #}
    {% for category in minorCategories %}
    <div class="minor-category-info" data-category-id="{{ category.id }}" style="display: none;">
        <div class="alert alert-info">
            <strong>{{ category.name }}</strong>: {{ category.description }}
            <ul class="mb-0 mt-2">
                {% if category.max_hours_per_day %}
                <li>{{ __('registration.max_hours', [category.max_hours_per_day]) }}</li>
                {% endif %}
                {% if category.min_shift_start_hour is not null %}
                <li>{{ __('registration.work_hours', [category.min_shift_start_hour ~ ':00', category.max_shift_end_hour ~ ':00']) }}</li>
                {% endif %}
                {% if category.requires_supervisor %}
                <li>{{ __('registration.requires_supervisor') }}</li>
                {% endif %}
            </ul>
        </div>
    </div>
    {% endfor %}
</div>
{% endif %}
```

### 4. Guardian Linking Page

New page at `/register/link-guardian` for minors to:
1. Enter guardian's email or username
2. Send a link request to the guardian
3. Option to skip and link later (with warning)

### 5. New Routes

```php
// In config/routes.php, add:
$route->get('/register/link-guardian', 'RegistrationController@linkGuardian');
$route->post('/register/link-guardian', 'RegistrationController@saveLinkGuardian');
```

## Test Plan

1. **Unit Tests**:
   - Test registration without minor category (adult)
   - Test registration with minor category (redirect to link-guardian)
   - Test minor category validation
   - Test guardian link request creation

2. **Integration Tests**:
   - Full registration flow for minor
   - Guardian receives and accepts link request

## Translation Keys to Add

```
registration.age_category = "Age Category"
registration.age_category_info = "Select your age category. Minors under 18 have additional protections."
registration.minor_category = "I am..."
registration.i_am_adult = "18 years or older (Adult)"
registration.minor_category_hint = "Your category determines work hours and supervision requirements"
registration.max_hours = "Maximum %d hours per day"
registration.work_hours = "Work hours: %s to %s"
registration.requires_supervisor = "Requires adult supervisor on shift"
registration.minor.link_guardian = "Please link a guardian to complete your registration"
```

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| Minor selects wrong category | Clear descriptions, guardian can change later |
| Guardian linking abandoned | Periodic reminder, minor cannot sign up for shifts until consent verified |
| Config flag not set | Default to enabled, fail-safe behavior |

## Definition of Done

- [ ] Minor category dropdown appears on registration form
- [ ] Category descriptions show restrictions clearly
- [ ] Minor registration redirects to guardian linking page
- [ ] Adult registration unchanged
- [ ] All unit tests pass
- [ ] Full test suite passes (1318 tests)
