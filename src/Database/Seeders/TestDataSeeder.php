<?php

declare(strict_types=1);

namespace Engelsystem\Database\Seeders;

use Carbon\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Group;
use Engelsystem\Models\Location;
use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\Privilege;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Engelsystem\Models\UserGuardian;
use Engelsystem\Models\UserSupervisorStatus;
use Illuminate\Database\Connection;
use Illuminate\Support\Str;

/**
 * Test Data Seeder for Minor Volunteer Support feature testing.
 *
 * Creates a comprehensive set of test data including:
 * - Users with various roles (admin, angels, guardians, minors)
 * - Angel types with different work categories
 * - Locations and shift types
 * - Shifts with relative dates (yesterday, today, tomorrow, +2 days)
 * - Guardian relationships and supervisor status
 * - Sample shift entries
 *
 * All test data uses the `test_` prefix for easy identification and cleanup.
 */
class TestDataSeeder
{
    private const TEST_PASSWORD = 'testpass123';
    private const TEST_PREFIX = 'test_';

    private Connection $db;
    private Carbon $today;

    /** @var array<string, int> Cached IDs for created entities */
    private array $userIds = [];
    private array $angelTypeIds = [];
    private array $locationIds = [];
    private array $shiftTypeIds = [];
    private array $shiftIds = [];
    private array $minorCategoryIds = [];

    private bool $verbose;

    public function __construct(Connection $db, bool $verbose = true)
    {
        $this->db = $db;
        $this->verbose = $verbose;
        $this->today = Carbon::today();
    }

    /**
     * Run the seeder to create all test data.
     */
    public function run(): void
    {
        $this->log('Starting TestDataSeeder...');
        $this->log('Base date: ' . $this->today->toDateString());
        $this->log('');

        // First clear existing test data
        $this->clear();

        // Load minor category IDs
        $this->loadMinorCategories();

        // Create entities in FK order
        $this->seedLocations();
        $this->seedAngelTypes();
        $this->seedShiftTypes();
        $this->seedUsers();
        $this->seedUserGroups();
        $this->seedUserAngelTypes();
        $this->seedShifts();
        $this->seedNeededAngelTypes();
        $this->seedGuardianRelationships();
        $this->seedSupervisorStatus();
        $this->seedShiftEntries();

        $this->log('');
        $this->log('TestDataSeeder completed successfully!');
        $this->log('');
        $this->log('Test user credentials (all users):');
        $this->log('  Password: ' . self::TEST_PASSWORD);
        $this->log('');
        $this->log('Quick test scenarios:');
        $this->log('  - Login as test_guardian_a to manage test_minor_junior');
        $this->log('  - Login as test_minor_teen to test shift signup restrictions');
        $this->log('  - Login as test_shico for Heaven staff perspective');
    }

    /**
     * Clear all test data (entities with test_ prefix).
     */
    public function clear(): void
    {
        $this->log('Clearing existing test data...');

        // Delete in reverse FK order
        $this->db->table('shift_entries')
            ->whereIn('shift_id', function ($query) {
                $query->select('id')->from('shifts')
                    ->where('title', 'like', self::TEST_PREFIX . '%');
            })
            ->orWhereIn('user_id', function ($query) {
                $query->select('id')->from('users')
                    ->where('name', 'like', self::TEST_PREFIX . '%');
            })
            ->delete();

        $this->db->table('needed_angel_types')
            ->whereIn('shift_id', function ($query) {
                $query->select('id')->from('shifts')
                    ->where('title', 'like', self::TEST_PREFIX . '%');
            })
            ->delete();

        $this->db->table('shifts')
            ->where('title', 'like', self::TEST_PREFIX . '%')
            ->delete();

        $this->db->table('user_guardian')
            ->whereIn('minor_user_id', function ($query) {
                $query->select('id')->from('users')
                    ->where('name', 'like', self::TEST_PREFIX . '%');
            })
            ->orWhereIn('guardian_user_id', function ($query) {
                $query->select('id')->from('users')
                    ->where('name', 'like', self::TEST_PREFIX . '%');
            })
            ->delete();

        $this->db->table('user_supervisor_status')
            ->whereIn('user_id', function ($query) {
                $query->select('id')->from('users')
                    ->where('name', 'like', self::TEST_PREFIX . '%');
            })
            ->delete();

        $this->db->table('user_angel_type')
            ->whereIn('user_id', function ($query) {
                $query->select('id')->from('users')
                    ->where('name', 'like', self::TEST_PREFIX . '%');
            })
            ->delete();

        $this->db->table('users_groups')
            ->whereIn('user_id', function ($query) {
                $query->select('id')->from('users')
                    ->where('name', 'like', self::TEST_PREFIX . '%');
            })
            ->delete();

        // Delete user related tables
        foreach (['users_contact', 'users_personal_data', 'users_settings', 'users_state'] as $table) {
            $this->db->table($table)
                ->whereIn('user_id', function ($query) {
                    $query->select('id')->from('users')
                        ->where('name', 'like', self::TEST_PREFIX . '%');
                })
                ->delete();
        }

        $this->db->table('users')
            ->where('name', 'like', self::TEST_PREFIX . '%')
            ->delete();

        $this->db->table('shift_types')
            ->where('name', 'like', self::TEST_PREFIX . '%')
            ->delete();

        $this->db->table('angel_types')
            ->where('name', 'like', self::TEST_PREFIX . '%')
            ->delete();

        $this->db->table('locations')
            ->where('name', 'like', self::TEST_PREFIX . '%')
            ->delete();

        $this->log('  Cleared.');
    }

    /**
     * Load existing minor categories.
     */
    private function loadMinorCategories(): void
    {
        $categories = MinorCategory::all();
        foreach ($categories as $category) {
            $this->minorCategoryIds[$category->name] = $category->id;
        }
        $this->log('Loaded ' . count($this->minorCategoryIds) . ' minor categories');
    }

    /**
     * Seed test locations.
     */
    private function seedLocations(): void
    {
        $this->log('Creating locations...');

        $locations = [
            [
                'name' => self::TEST_PREFIX . 'Heaven Helpdesk',
                'dect' => '1023',
                'description' => 'Central coordination point for all angels',
            ],
            [
                'name' => self::TEST_PREFIX . 'Kidspace Hall B',
                'dect' => '1543',
                'description' => 'Child-friendly area with activities',
            ],
            [
                'name' => self::TEST_PREFIX . 'Main Hall Stage',
                'dect' => '1500',
                'description' => 'Primary lecture hall',
            ],
            [
                'name' => self::TEST_PREFIX . 'Bar Rubiqs',
                'dect' => '1720',
                'description' => 'Adult-only venue',
            ],
        ];

        foreach ($locations as $data) {
            $location = Location::create($data);
            // Store with a short key
            $key = str_replace(self::TEST_PREFIX, '', $data['name']);
            $this->locationIds[$key] = $location->id;
            $this->log('  Created location: ' . $data['name']);
        }
    }

    /**
     * Seed test angel types with work categories.
     */
    private function seedAngelTypes(): void
    {
        $this->log('Creating angel types...');

        $angelTypes = [
            [
                'name' => self::TEST_PREFIX . 'Infodesk Angel',
                'description' => 'Light work at the information desk. Suitable for all volunteers.',
                'restricted' => false,
                'shift_self_signup' => true,
                'show_on_dashboard' => true,
            ],
            [
                'name' => self::TEST_PREFIX . 'Kidspace Angel',
                'description' => 'Light work in the child-friendly area. Suitable for junior volunteers.',
                'restricted' => false,
                'shift_self_signup' => true,
                'show_on_dashboard' => true,
            ],
            [
                'name' => self::TEST_PREFIX . 'Herald Angel',
                'description' => 'Standard work supporting lectures and presentations.',
                'restricted' => false,
                'shift_self_signup' => true,
                'show_on_dashboard' => true,
            ],
            [
                'name' => self::TEST_PREFIX . 'CERT',
                'description' => 'Emergency response team. Requires training.',
                'restricted' => true,
                'shift_self_signup' => false,
                'show_on_dashboard' => true,
            ],
            [
                'name' => self::TEST_PREFIX . 'Bar Angel',
                'description' => 'Bar service. Adults only due to alcohol handling.',
                'restricted' => false,
                'shift_self_signup' => true,
                'show_on_dashboard' => true,
            ],
            [
                'name' => self::TEST_PREFIX . 'Night Security',
                'description' => 'Night-time security patrol. Adults only.',
                'restricted' => true,
                'shift_self_signup' => false,
                'show_on_dashboard' => true,
            ],
        ];

        foreach ($angelTypes as $data) {
            $angelType = AngelType::create($data);
            $key = str_replace(self::TEST_PREFIX, '', $data['name']);
            $this->angelTypeIds[$key] = $angelType->id;
            $this->log('  Created angel type: ' . $data['name']);
        }
    }

    /**
     * Seed test shift types with work categories.
     */
    private function seedShiftTypes(): void
    {
        $this->log('Creating shift types...');

        $shiftTypes = [
            [
                'name' => self::TEST_PREFIX . 'Infodesk Shift',
                'description' => 'Staffing the information desk',
                'work_category' => 'A',
                'allows_accompanying_children' => false,
            ],
            [
                'name' => self::TEST_PREFIX . 'Kidspace Operation',
                'description' => 'Running activities in the kidspace',
                'work_category' => 'A',
                'allows_accompanying_children' => true,
            ],
            [
                'name' => self::TEST_PREFIX . 'Herald Shift',
                'description' => 'Supporting lectures and talks',
                'work_category' => 'B',
                'allows_accompanying_children' => false,
            ],
            [
                'name' => self::TEST_PREFIX . 'CERT Shift',
                'description' => 'Emergency response duty',
                'work_category' => 'B',
                'allows_accompanying_children' => false,
            ],
            [
                'name' => self::TEST_PREFIX . 'Bar Shift',
                'description' => 'Bar service duty',
                'work_category' => 'C',
                'allows_accompanying_children' => false,
            ],
        ];

        foreach ($shiftTypes as $data) {
            $shiftType = ShiftType::create($data);
            $key = str_replace(self::TEST_PREFIX, '', $data['name']);
            $this->shiftTypeIds[$key] = $shiftType->id;
            $this->log('  Created shift type: ' . $data['name']);
        }
    }

    /**
     * Seed test users with various roles and minor categories.
     */
    private function seedUsers(): void
    {
        $this->log('Creating users...');

        $users = [
            // Staff users
            [
                'name' => 'test_shico',
                'email' => 'test_shico@example.com',
                'minor_category' => null,
                'group_id' => 60, // ShiCo
            ],
            [
                'name' => 'test_bureaucrat',
                'email' => 'test_bureaucrat@example.com',
                'minor_category' => null,
                'group_id' => 80, // Bureaucrat
            ],
            // Regular adult angels
            [
                'name' => 'test_angel_a',
                'email' => 'test_angel_a@example.com',
                'minor_category' => null,
                'group_id' => 20, // Angel
            ],
            [
                'name' => 'test_angel_b',
                'email' => 'test_angel_b@example.com',
                'minor_category' => null,
                'group_id' => 20,
            ],
            // Supervisors
            [
                'name' => 'test_supervisor_a',
                'email' => 'test_supervisor_a@example.com',
                'minor_category' => null,
                'group_id' => 20,
            ],
            [
                'name' => 'test_supervisor_b',
                'email' => 'test_supervisor_b@example.com',
                'minor_category' => null,
                'group_id' => 20,
            ],
            // Guardians
            [
                'name' => 'test_guardian_a',
                'email' => 'test_guardian_a@example.com',
                'minor_category' => null,
                'group_id' => 20,
            ],
            [
                'name' => 'test_guardian_b',
                'email' => 'test_guardian_b@example.com',
                'minor_category' => null,
                'group_id' => 20,
            ],
            // Minor users
            [
                'name' => 'test_minor_junior',
                'email' => 'test_minor_junior@example.com',
                'minor_category' => 'Junior Angel',
                'group_id' => 20,
            ],
            [
                'name' => 'test_minor_teen',
                'email' => 'test_minor_teen@example.com',
                'minor_category' => 'Teen Angel',
                'group_id' => 20,
            ],
            [
                'name' => 'test_minor_child',
                'email' => 'test_minor_child@example.com',
                'minor_category' => 'Accompanying Child',
                'group_id' => 20,
            ],
        ];

        foreach ($users as $userData) {
            $minorCategoryId = $this->minorCategoryIds[$userData['minor_category']] ?? null;

            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => password_hash(self::TEST_PASSWORD, PASSWORD_DEFAULT),
                'api_key' => Str::random(64),
                'minor_category_id' => $minorCategoryId,
            ]);

            // Create related records
            Contact::create(['user_id' => $user->id, 'dect' => null, 'mobile' => null]);
            PersonalData::create(['user_id' => $user->id]);
            Settings::create(['user_id' => $user->id, 'language' => 'en_US', 'theme' => 1]);
            State::create([
                'user_id' => $user->id,
                'arrival_date' => $this->today,
                'active' => true,
                'got_goodie' => false,
                'force_active' => false,
            ]);

            $this->userIds[$userData['name']] = $user->id;
            $this->log('  Created user: ' . $userData['name'] . ' (' . $userData['minor_category'] . ')');
        }
    }

    /**
     * Assign users to groups for permissions.
     */
    private function seedUserGroups(): void
    {
        $this->log('Assigning user groups...');

        $groupAssignments = [
            'test_shico' => 60,      // ShiCo
            'test_bureaucrat' => 80, // Bureaucrat
            'test_angel_a' => 20,    // Angel
            'test_angel_b' => 20,
            'test_supervisor_a' => 20,
            'test_supervisor_b' => 20,
            'test_guardian_a' => 20,
            'test_guardian_b' => 20,
            'test_minor_junior' => 20,
            'test_minor_teen' => 20,
            'test_minor_child' => 20,
        ];

        foreach ($groupAssignments as $userName => $groupId) {
            if (isset($this->userIds[$userName])) {
                $this->db->table('users_groups')->insert([
                    'user_id' => $this->userIds[$userName],
                    'group_id' => $groupId,
                ]);
            }
        }

        // Also grant user_guardian privilege to guardians
        $guardianPrivilege = Privilege::where('name', 'user_guardian')->first();
        if ($guardianPrivilege) {
            foreach (['test_guardian_a', 'test_guardian_b'] as $guardianName) {
                if (isset($this->userIds[$guardianName])) {
                    // Add guardian privilege via groups_privileges if not already there
                    // For now, we assume guardians get this through their angel group
                }
            }
        }

        $this->log('  Groups assigned.');
    }

    /**
     * Assign users to angel types.
     */
    private function seedUserAngelTypes(): void
    {
        $this->log('Assigning angel type memberships...');

        // All adults get basic angel types
        $adultUsers = [
            'test_shico', 'test_bureaucrat', 'test_angel_a', 'test_angel_b',
            'test_supervisor_a', 'test_supervisor_b', 'test_guardian_a', 'test_guardian_b',
        ];

        foreach ($adultUsers as $userName) {
            if (!isset($this->userIds[$userName])) {
                continue;
            }

            // Basic angel types for all adults
            foreach (['Infodesk Angel', 'Kidspace Angel'] as $angelTypeName) {
                if (isset($this->angelTypeIds[$angelTypeName])) {
                    UserAngelType::create([
                        'user_id' => $this->userIds[$userName],
                        'angel_type_id' => $this->angelTypeIds[$angelTypeName],
                        'confirm_user_id' => $this->userIds['test_shico'] ?? null,
                    ]);
                }
            }
        }

        // Supervisors and guardians also get Herald
        foreach (['test_supervisor_a', 'test_supervisor_b', 'test_guardian_a', 'test_guardian_b'] as $userName) {
            if (isset($this->userIds[$userName]) && isset($this->angelTypeIds['Herald Angel'])) {
                UserAngelType::create([
                    'user_id' => $this->userIds[$userName],
                    'angel_type_id' => $this->angelTypeIds['Herald Angel'],
                    'confirm_user_id' => $this->userIds['test_shico'] ?? null,
                ]);
            }
        }

        // test_angel_a gets adult-only types
        if (isset($this->userIds['test_angel_a'])) {
            foreach (['Bar Angel', 'Night Security'] as $angelTypeName) {
                if (isset($this->angelTypeIds[$angelTypeName])) {
                    UserAngelType::create([
                        'user_id' => $this->userIds['test_angel_a'],
                        'angel_type_id' => $this->angelTypeIds[$angelTypeName],
                        'confirm_user_id' => $this->userIds['test_shico'] ?? null,
                    ]);
                }
            }
        }

        // Minors get appropriate angel types based on their category
        // Junior Angel (category A only)
        if (isset($this->userIds['test_minor_junior'])) {
            foreach (['Infodesk Angel', 'Kidspace Angel'] as $angelTypeName) {
                if (isset($this->angelTypeIds[$angelTypeName])) {
                    UserAngelType::create([
                        'user_id' => $this->userIds['test_minor_junior'],
                        'angel_type_id' => $this->angelTypeIds[$angelTypeName],
                        'confirm_user_id' => $this->userIds['test_guardian_a'] ?? null,
                    ]);
                }
            }
        }

        // Teen Angel (category A+B)
        if (isset($this->userIds['test_minor_teen'])) {
            foreach (['Infodesk Angel', 'Kidspace Angel', 'Herald Angel'] as $angelTypeName) {
                if (isset($this->angelTypeIds[$angelTypeName])) {
                    UserAngelType::create([
                        'user_id' => $this->userIds['test_minor_teen'],
                        'angel_type_id' => $this->angelTypeIds[$angelTypeName],
                        'confirm_user_id' => $this->userIds['test_guardian_b'] ?? null,
                    ]);
                }
            }
        }

        $this->log('  Angel types assigned.');
    }

    /**
     * Seed test shifts with relative dates.
     */
    private function seedShifts(): void
    {
        $this->log('Creating shifts...');

        $yesterday = $this->today->copy()->subDay();
        $tomorrow = $this->today->copy()->addDay();
        $dayAfterTomorrow = $this->today->copy()->addDays(2);

        // Get admin user for created_by
        $adminId = User::where('name', 'admin')->first()?->id ?? $this->userIds['test_shico'] ?? 1;

        $shifts = [
            // Yesterday (past shifts)
            [
                'title' => self::TEST_PREFIX . 'Infodesk Morning (Past)',
                'shift_type_id' => $this->shiftTypeIds['Infodesk Shift'] ?? 1,
                'location_id' => $this->locationIds['Heaven Helpdesk'] ?? 1,
                'start' => $yesterday->copy()->setTime(10, 0),
                'end' => $yesterday->copy()->setTime(12, 0),
                'requires_supervisor_for_minors' => true,
            ],
            [
                'title' => self::TEST_PREFIX . 'Infodesk Afternoon (Past)',
                'shift_type_id' => $this->shiftTypeIds['Infodesk Shift'] ?? 1,
                'location_id' => $this->locationIds['Heaven Helpdesk'] ?? 1,
                'start' => $yesterday->copy()->setTime(14, 0),
                'end' => $yesterday->copy()->setTime(16, 0),
                'requires_supervisor_for_minors' => true,
            ],
            // Today
            [
                'title' => self::TEST_PREFIX . 'Kidspace Morning',
                'shift_type_id' => $this->shiftTypeIds['Kidspace Operation'] ?? 1,
                'location_id' => $this->locationIds['Kidspace Hall B'] ?? 1,
                'start' => $this->today->copy()->setTime(10, 0),
                'end' => $this->today->copy()->setTime(12, 0),
                'requires_supervisor_for_minors' => true,
            ],
            [
                'title' => self::TEST_PREFIX . 'Kidspace Afternoon',
                'shift_type_id' => $this->shiftTypeIds['Kidspace Operation'] ?? 1,
                'location_id' => $this->locationIds['Kidspace Hall B'] ?? 1,
                'start' => $this->today->copy()->setTime(14, 0),
                'end' => $this->today->copy()->setTime(16, 0),
                'requires_supervisor_for_minors' => true,
            ],
            [
                'title' => self::TEST_PREFIX . 'Herald Evening',
                'shift_type_id' => $this->shiftTypeIds['Herald Shift'] ?? 1,
                'location_id' => $this->locationIds['Main Hall Stage'] ?? 1,
                'start' => $this->today->copy()->setTime(18, 0),
                'end' => $this->today->copy()->setTime(20, 0),
                'requires_supervisor_for_minors' => true,
            ],
            // Tomorrow
            [
                'title' => self::TEST_PREFIX . 'Junior-Safe Morning',
                'shift_type_id' => $this->shiftTypeIds['Infodesk Shift'] ?? 1,
                'location_id' => $this->locationIds['Heaven Helpdesk'] ?? 1,
                'start' => $tomorrow->copy()->setTime(9, 0),
                'end' => $tomorrow->copy()->setTime(11, 0),
                'requires_supervisor_for_minors' => true,
            ],
            [
                'title' => self::TEST_PREFIX . 'Teen-Extended Evening',
                'shift_type_id' => $this->shiftTypeIds['Herald Shift'] ?? 1,
                'location_id' => $this->locationIds['Main Hall Stage'] ?? 1,
                'start' => $tomorrow->copy()->setTime(16, 0),
                'end' => $tomorrow->copy()->setTime(20, 0),
                'requires_supervisor_for_minors' => true,
            ],
            [
                'title' => self::TEST_PREFIX . 'Too-Early Shift',
                'shift_type_id' => $this->shiftTypeIds['Infodesk Shift'] ?? 1,
                'location_id' => $this->locationIds['Heaven Helpdesk'] ?? 1,
                'start' => $tomorrow->copy()->setTime(5, 0),
                'end' => $tomorrow->copy()->setTime(7, 0),
                'requires_supervisor_for_minors' => false,
            ],
            [
                'title' => self::TEST_PREFIX . 'Too-Late Shift',
                'shift_type_id' => $this->shiftTypeIds['Herald Shift'] ?? 1,
                'location_id' => $this->locationIds['Main Hall Stage'] ?? 1,
                'start' => $tomorrow->copy()->setTime(20, 0),
                'end' => $tomorrow->copy()->setTime(22, 0),
                'requires_supervisor_for_minors' => false,
            ],
            [
                'title' => self::TEST_PREFIX . 'Night Bar Shift',
                'shift_type_id' => $this->shiftTypeIds['Bar Shift'] ?? 1,
                'location_id' => $this->locationIds['Bar Rubiqs'] ?? 1,
                'start' => $tomorrow->copy()->setTime(22, 0),
                'end' => $tomorrow->copy()->addDay()->setTime(2, 0),
                'requires_supervisor_for_minors' => false,
            ],
            // Day after tomorrow
            [
                'title' => self::TEST_PREFIX . 'Long Kidspace Day',
                'shift_type_id' => $this->shiftTypeIds['Kidspace Operation'] ?? 1,
                'location_id' => $this->locationIds['Kidspace Hall B'] ?? 1,
                'start' => $dayAfterTomorrow->copy()->setTime(8, 0),
                'end' => $dayAfterTomorrow->copy()->setTime(18, 0),
                'requires_supervisor_for_minors' => true,
            ],
            [
                'title' => self::TEST_PREFIX . 'Overlapping Shift',
                'shift_type_id' => $this->shiftTypeIds['Infodesk Shift'] ?? 1,
                'location_id' => $this->locationIds['Heaven Helpdesk'] ?? 1,
                'start' => $dayAfterTomorrow->copy()->setTime(10, 0),
                'end' => $dayAfterTomorrow->copy()->setTime(12, 0),
                'requires_supervisor_for_minors' => true,
            ],
        ];

        foreach ($shifts as $data) {
            $shift = Shift::create([
                'title' => $data['title'],
                'shift_type_id' => $data['shift_type_id'],
                'location_id' => $data['location_id'],
                'start' => $data['start'],
                'end' => $data['end'],
                'requires_supervisor_for_minors' => $data['requires_supervisor_for_minors'],
                'created_by' => $adminId,
            ]);

            $key = str_replace(self::TEST_PREFIX, '', $data['title']);
            $this->shiftIds[$key] = $shift->id;
            $this->log('  Created shift: ' . $data['title']);
        }
    }

    /**
     * Link shifts to needed angel types.
     */
    private function seedNeededAngelTypes(): void
    {
        $this->log('Creating needed angel types for shifts...');

        // Map shifts to their primary angel types and slot counts
        $shiftAngelTypes = [
            'Infodesk Morning (Past)' => ['Infodesk Angel' => 3],
            'Infodesk Afternoon (Past)' => ['Infodesk Angel' => 3],
            'Kidspace Morning' => ['Kidspace Angel' => 4],
            'Kidspace Afternoon' => ['Kidspace Angel' => 4],
            'Herald Evening' => ['Herald Angel' => 3],
            'Junior-Safe Morning' => ['Infodesk Angel' => 3],
            'Teen-Extended Evening' => ['Herald Angel' => 3],
            'Too-Early Shift' => ['Infodesk Angel' => 2],
            'Too-Late Shift' => ['Herald Angel' => 2],
            'Night Bar Shift' => ['Bar Angel' => 3],
            'Long Kidspace Day' => ['Kidspace Angel' => 5],
            'Overlapping Shift' => ['Infodesk Angel' => 3],
        ];

        foreach ($shiftAngelTypes as $shiftKey => $angelTypes) {
            if (!isset($this->shiftIds[$shiftKey])) {
                continue;
            }

            foreach ($angelTypes as $angelTypeName => $count) {
                if (!isset($this->angelTypeIds[$angelTypeName])) {
                    continue;
                }

                NeededAngelType::create([
                    'shift_id' => $this->shiftIds[$shiftKey],
                    'angel_type_id' => $this->angelTypeIds[$angelTypeName],
                    'count' => $count,
                ]);
            }
        }

        $this->log('  Needed angel types created.');
    }

    /**
     * Create guardian-minor relationships.
     */
    private function seedGuardianRelationships(): void
    {
        $this->log('Creating guardian relationships...');

        $relationships = [
            // test_guardian_a is primary guardian for test_minor_junior
            [
                'guardian' => 'test_guardian_a',
                'minor' => 'test_minor_junior',
                'is_primary' => true,
                'relationship_type' => 'parent',
            ],
            // test_guardian_b is primary guardian for test_minor_teen and test_minor_child
            [
                'guardian' => 'test_guardian_b',
                'minor' => 'test_minor_teen',
                'is_primary' => true,
                'relationship_type' => 'parent',
            ],
            [
                'guardian' => 'test_guardian_b',
                'minor' => 'test_minor_child',
                'is_primary' => true,
                'relationship_type' => 'parent',
            ],
            // test_supervisor_a is secondary guardian for test_minor_teen (delegated authority)
            [
                'guardian' => 'test_supervisor_a',
                'minor' => 'test_minor_teen',
                'is_primary' => false,
                'relationship_type' => 'delegated',
            ],
        ];

        foreach ($relationships as $rel) {
            if (!isset($this->userIds[$rel['guardian']]) || !isset($this->userIds[$rel['minor']])) {
                continue;
            }

            UserGuardian::create([
                'guardian_user_id' => $this->userIds[$rel['guardian']],
                'minor_user_id' => $this->userIds[$rel['minor']],
                'is_primary' => $rel['is_primary'],
                'relationship_type' => $rel['relationship_type'],
                'can_manage_account' => $rel['is_primary'],
                'valid_from' => $this->today->copy()->subDays(30),
                'valid_until' => $this->today->copy()->addDays(30),
            ]);

            $this->log('  ' . $rel['guardian'] . ' -> ' . $rel['minor'] . ' (' . $rel['relationship_type'] . ')');
        }
    }

    /**
     * Create supervisor status records.
     */
    private function seedSupervisorStatus(): void
    {
        $this->log('Creating supervisor status records...');

        $supervisorStatus = [
            'test_supervisor_a' => ['willing' => true, 'trained' => true],
            'test_supervisor_b' => ['willing' => false, 'trained' => false],
            'test_guardian_a' => ['willing' => true, 'trained' => false],
            'test_guardian_b' => ['willing' => true, 'trained' => false],
        ];

        foreach ($supervisorStatus as $userName => $status) {
            if (!isset($this->userIds[$userName])) {
                continue;
            }

            UserSupervisorStatus::create([
                'user_id' => $this->userIds[$userName],
                'willing_to_supervise' => $status['willing'],
                'supervision_training_completed' => $status['trained'],
            ]);

            $willingText = $status['willing'] ? 'willing' : 'not willing';
            $this->log('  ' . $userName . ': ' . $willingText);
        }
    }

    /**
     * Create sample shift entries for testing.
     */
    private function seedShiftEntries(): void
    {
        $this->log('Creating shift entries...');

        // Pre-create some shift entries for testing
        $entries = [
            // Supervisor signed up for Kidspace Morning
            [
                'shift' => 'Kidspace Morning',
                'user' => 'test_supervisor_a',
                'angel_type' => 'Kidspace Angel',
                'counts_toward_quota' => true,
                'supervised_by' => null,
            ],
            // Guardian signed up for same shift
            [
                'shift' => 'Kidspace Morning',
                'user' => 'test_guardian_a',
                'angel_type' => 'Kidspace Angel',
                'counts_toward_quota' => true,
                'supervised_by' => null,
            ],
            // Minor junior participated in past shift (supervised by guardian)
            [
                'shift' => 'Infodesk Morning (Past)',
                'user' => 'test_minor_junior',
                'angel_type' => 'Infodesk Angel',
                'counts_toward_quota' => true,
                'supervised_by' => 'test_guardian_a',
            ],
            // Minor teen signed up for Herald Evening (supervised by supervisor_a)
            [
                'shift' => 'Herald Evening',
                'user' => 'test_minor_teen',
                'angel_type' => 'Herald Angel',
                'counts_toward_quota' => true,
                'supervised_by' => 'test_supervisor_a',
            ],
        ];

        foreach ($entries as $entry) {
            if (!isset($this->shiftIds[$entry['shift']]) ||
                !isset($this->userIds[$entry['user']]) ||
                !isset($this->angelTypeIds[$entry['angel_type']])) {
                continue;
            }

            $supervisedById = null;
            if ($entry['supervised_by'] && isset($this->userIds[$entry['supervised_by']])) {
                $supervisedById = $this->userIds[$entry['supervised_by']];
            }

            ShiftEntry::create([
                'shift_id' => $this->shiftIds[$entry['shift']],
                'user_id' => $this->userIds[$entry['user']],
                'angel_type_id' => $this->angelTypeIds[$entry['angel_type']],
                'counts_toward_quota' => $entry['counts_toward_quota'],
                'supervised_by_user_id' => $supervisedById,
            ]);

            $this->log('  ' . $entry['user'] . ' -> ' . $entry['shift']);
        }
    }

    /**
     * Log a message if verbose mode is enabled.
     */
    private function log(string $message): void
    {
        if ($this->verbose) {
            echo $message . PHP_EOL;
        }
    }
}
