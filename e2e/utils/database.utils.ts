import { execSync } from 'child_process';

/**
 * Database utilities for E2E tests
 *
 * These utilities allow tests to interact with the database directly
 * for setup, teardown, and verification purposes.
 *
 * Note: These use mysql CLI via shell commands. For CI, ensure mysql client is available.
 */

// Database connection config from environment or defaults
const DB_HOST = process.env.MYSQL_HOST || '192.168.105.2';
const DB_PORT = process.env.MYSQL_PORT || '31402';
const DB_USER = process.env.MYSQL_USER || 'engelsystem';
const DB_PASS = process.env.MYSQL_PASSWORD || 'engelsystem';
const DB_NAME = process.env.MYSQL_DATABASE || 'engelsystem';

/**
 * Execute a SQL query and return the result as a string
 */
export function execQuery(sql: string): string {
  const cmd = `mysql -h ${DB_HOST} -P ${DB_PORT} -u ${DB_USER} -p${DB_PASS} ${DB_NAME} --skip-ssl -e "${sql.replace(/"/g, '\\"')}"`;
  try {
    return execSync(cmd, { encoding: 'utf-8', stdio: ['pipe', 'pipe', 'pipe'] });
  } catch (error: unknown) {
    const err = error as { stderr?: string; message?: string };
    console.error('Database query failed:', err.stderr || err.message);
    throw error;
  }
}

/**
 * Execute a SQL query and return results as parsed rows
 */
export function queryRows<T = Record<string, string>>(sql: string): T[] {
  const result = execQuery(sql);
  const lines = result.trim().split('\n');
  if (lines.length < 2) return [];

  const headers = lines[0].split('\t');
  return lines.slice(1).map((line) => {
    const values = line.split('\t');
    const row: Record<string, string> = {};
    headers.forEach((header, index) => {
      row[header] = values[index] || '';
    });
    return row as T;
  });
}

/**
 * Get a user by username
 */
export function getUserByUsername(username: string): {
  id: string;
  name: string;
  email: string;
  minor_category_id: string | null;
} | null {
  const rows = queryRows<{
    id: string;
    name: string;
    email: string;
    minor_category_id: string;
  }>(`SELECT id, name, email, minor_category_id FROM users WHERE name = '${username}' LIMIT 1`);
  return rows[0] || null;
}

/**
 * Get a user by ID
 */
export function getUserById(userId: number): {
  id: string;
  name: string;
  email: string;
  minor_category_id: string | null;
} | null {
  const rows = queryRows<{
    id: string;
    name: string;
    email: string;
    minor_category_id: string;
  }>(`SELECT id, name, email, minor_category_id FROM users WHERE id = ${userId} LIMIT 1`);
  return rows[0] || null;
}

/**
 * Get all shift entries for a user
 */
export function getShiftEntriesForUser(userId: number): Array<{
  id: string;
  shift_id: string;
  user_id: string;
  angel_type_id: string;
}> {
  return queryRows(`SELECT id, shift_id, user_id, angel_type_id FROM shift_entries WHERE user_id = ${userId}`);
}

/**
 * Get a shift by ID
 */
export function getShiftById(shiftId: number): {
  id: string;
  title: string;
  shift_type_id: string;
  location_id: string;
  start: string;
  end: string;
} | null {
  const rows = queryRows<{
    id: string;
    title: string;
    shift_type_id: string;
    location_id: string;
    start: string;
    end: string;
  }>(`SELECT id, title, shift_type_id, location_id, start, end FROM shifts WHERE id = ${shiftId} LIMIT 1`);
  return rows[0] || null;
}

/**
 * Get shift type by ID
 */
export function getShiftTypeById(shiftTypeId: number): {
  id: string;
  name: string;
  requires_supervisor_for_minor: string;
} | null {
  const rows = queryRows<{
    id: string;
    name: string;
    requires_supervisor_for_minor: string;
  }>(
    `SELECT id, name, requires_supervisor_for_minor FROM shift_types WHERE id = ${shiftTypeId} LIMIT 1`
  );
  return rows[0] || null;
}

/**
 * Get minor categories
 */
export function getMinorCategories(): Array<{
  id: string;
  name: string;
  min_age: string;
  max_age: string;
  max_hours_per_day: string;
  requires_supervisor: string;
}> {
  return queryRows(`SELECT id, name, min_age, max_age, max_hours_per_day, requires_supervisor FROM minor_categories WHERE is_active = 1 ORDER BY display_order`);
}

/**
 * Get guardian relationships for a minor
 */
export function getGuardianRelationships(minorUserId: number): Array<{
  id: string;
  guardian_id: string;
  minor_id: string;
  relationship_type: string;
  is_primary: string;
}> {
  return queryRows(
    `SELECT id, guardian_id, minor_id, relationship_type, is_primary FROM minor_guardians WHERE minor_id = ${minorUserId}`
  );
}

/**
 * Check if a user is a minor (has minor_category_id set)
 */
export function isMinor(userId: number): boolean {
  const user = getUserById(userId);
  return user !== null && user.minor_category_id !== null && user.minor_category_id !== '';
}

/**
 * Check if consent is approved for a minor
 */
export function isConsentApproved(minorUserId: number): boolean {
  const rows = queryRows<{ consent_approved_by_user_id: string }>(
    `SELECT consent_approved_by_user_id FROM users WHERE id = ${minorUserId} LIMIT 1`
  );
  return rows.length > 0 && rows[0].consent_approved_by_user_id !== '' && rows[0].consent_approved_by_user_id !== null;
}

/**
 * Delete test data (users and related data with test_ prefix)
 * Useful for cleanup between tests
 */
export function cleanupTestData(): void {
  // Delete shift entries for test users
  execQuery(`DELETE FROM shift_entries WHERE user_id IN (SELECT id FROM users WHERE name LIKE 'test_%')`);

  // Delete guardian relationships for test users
  execQuery(
    `DELETE FROM minor_guardians WHERE minor_id IN (SELECT id FROM users WHERE name LIKE 'test_%') OR guardian_id IN (SELECT id FROM users WHERE name LIKE 'test_%')`
  );

  // Delete personal data for test users
  execQuery(`DELETE FROM users_personal_data WHERE user_id IN (SELECT id FROM users WHERE name LIKE 'test_%')`);

  // Delete test users
  execQuery(`DELETE FROM users WHERE name LIKE 'test_%'`);

  // Delete test shifts
  execQuery(`DELETE FROM shifts WHERE title LIKE 'test_%'`);

  console.log('Test data cleaned up');
}

/**
 * Reset all shift entries for a specific user
 */
export function resetUserShiftEntries(userId: number): void {
  execQuery(`DELETE FROM shift_entries WHERE user_id = ${userId}`);
}

/**
 * Get shifts that a minor can sign up for based on category restrictions
 */
export function getAvailableShiftsForMinor(minorUserId: number): Array<{
  shift_id: string;
  shift_title: string;
  shift_type_name: string;
  requires_supervisor: string;
}> {
  const user = getUserById(minorUserId);
  if (!user || !user.minor_category_id) return [];

  return queryRows(`
    SELECT
      s.id as shift_id,
      s.title as shift_title,
      st.name as shift_type_name,
      st.requires_supervisor_for_minor as requires_supervisor
    FROM shifts s
    JOIN shift_types st ON s.shift_type_id = st.id
    WHERE s.start > NOW()
    ORDER BY s.start
    LIMIT 20
  `);
}
