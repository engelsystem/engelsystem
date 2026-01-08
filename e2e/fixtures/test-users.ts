/**
 * Test user constants for E2E tests.
 * These users are created by the test data seeder (bin/seed-test-data).
 *
 * All test users use the password: testpass123
 */

export const TEST_PASSWORD = 'testpass123';

/**
 * Test user credentials and metadata
 */
export const TEST_USERS = {
  // Admin/Staff users
  shico: {
    username: 'test_shico',
    password: TEST_PASSWORD,
    role: 'ShiCo',
    description: 'Shift Coordinator - Heaven staff with admin privileges',
  },
  bureaucrat: {
    username: 'test_bureaucrat',
    password: TEST_PASSWORD,
    role: 'Bureaucrat',
    description: 'User admin for managing accounts',
  },

  // Regular adult angels
  angelA: {
    username: 'test_angel_a',
    password: TEST_PASSWORD,
    role: 'Angel',
    description: 'Standard adult volunteer',
  },
  angelB: {
    username: 'test_angel_b',
    password: TEST_PASSWORD,
    role: 'Angel',
    description: 'Standard adult volunteer',
  },

  // Guardians (adults who supervise minors)
  guardianA: {
    username: 'test_guardian_a',
    password: TEST_PASSWORD,
    role: 'Guardian',
    description: 'Guardian of test_minor_junior',
    linkedMinors: ['test_minor_junior'],
  },
  guardianB: {
    username: 'test_guardian_b',
    password: TEST_PASSWORD,
    role: 'Guardian',
    description: 'Guardian of test_minor_teen and test_minor_child',
    linkedMinors: ['test_minor_teen', 'test_minor_child'],
  },

  // Supervisors (adults willing to supervise minors on shifts)
  supervisorA: {
    username: 'test_supervisor_a',
    password: TEST_PASSWORD,
    role: 'Supervisor',
    description: 'Adult willing to supervise minors on shifts',
    willingToSupervise: true,
  },
  supervisorB: {
    username: 'test_supervisor_b',
    password: TEST_PASSWORD,
    role: 'Angel',
    description: 'Adult NOT willing to supervise',
    willingToSupervise: false,
  },

  // Minor volunteers
  minorJunior: {
    username: 'test_minor_junior',
    password: TEST_PASSWORD,
    role: 'Junior Angel',
    description: 'Junior Angel (13-14): 4h/day, 08:00-18:00, Category A only',
    minorCategory: 'Junior Angel',
    maxHoursPerDay: 4,
    allowedHours: { start: '08:00', end: '18:00' },
    workCategories: ['A'],
  },
  minorTeen: {
    username: 'test_minor_teen',
    password: TEST_PASSWORD,
    role: 'Teen Angel',
    description: 'Teen Angel (15-17): 8h/day, 06:00-22:00, Categories A+B',
    minorCategory: 'Teen Angel',
    maxHoursPerDay: 8,
    allowedHours: { start: '06:00', end: '22:00' },
    workCategories: ['A', 'B'],
  },
  minorChild: {
    username: 'test_minor_child',
    password: TEST_PASSWORD,
    role: 'Accompanying Child',
    description: 'Accompanying Child (under 13): No volunteer work, supervision only',
    minorCategory: 'Accompanying Child',
    maxHoursPerDay: 0,
    allowedHours: null,
    workCategories: [],
  },
} as const;

/**
 * Helper type for user keys
 */
export type TestUserKey = keyof typeof TEST_USERS;

/**
 * Get user credentials by key
 */
export function getUser(key: TestUserKey) {
  return TEST_USERS[key];
}

/**
 * Get all guardian users
 */
export function getGuardians() {
  return [TEST_USERS.guardianA, TEST_USERS.guardianB];
}

/**
 * Get all minor users
 */
export function getMinors() {
  return [TEST_USERS.minorJunior, TEST_USERS.minorTeen, TEST_USERS.minorChild];
}

/**
 * Get all supervisor users
 */
export function getSupervisors() {
  return [TEST_USERS.supervisorA];
}

/**
 * Get all admin/staff users
 */
export function getAdminUsers() {
  return [TEST_USERS.shico, TEST_USERS.bureaucrat];
}
