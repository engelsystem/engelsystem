# Test Data Infrastructure

This document describes the test data seeding infrastructure used for manual and automated testing of the Engelsystem, particularly the Minor Volunteer Support feature.

## Quick Start

```bash
# From nix develop shell
es-seed-test-data

# Or via nix run
nix run .#seed-test-data

# Clear test data only
es-seed-test-data --clear
```

## Overview

The test data seeder creates a comprehensive set of test data including:

- **12 users** with various roles and minor categories
- **6 angel types** with different work categories (A, B, C)
- **4 locations** with realistic DECT numbers
- **5 shift types** matching the work categories
- **12 shifts** spanning yesterday through tomorrow+2
- **Guardian relationships** linking minors to their guardians
- **Supervisor status** records for adults willing to supervise
- **Sample shift entries** demonstrating participation patterns

All test data uses the `test_` prefix for easy identification and cleanup.

## Test Users

| Username | Role | Minor Category | Password |
|----------|------|----------------|----------|
| `test_shico` | ShiCo (60) | *(none - adult)* | `testpass123` |
| `test_bureaucrat` | Bureaucrat (80) | *(none - adult)* | `testpass123` |
| `test_angel_a` | Angel (20) | *(none - adult)* | `testpass123` |
| `test_angel_b` | Angel (20) | *(none - adult)* | `testpass123` |
| `test_supervisor_a` | Angel (20) | *(none - adult)* | `testpass123` |
| `test_supervisor_b` | Angel (20) | *(none - adult)* | `testpass123` |
| `test_guardian_a` | Angel (20) | *(none - adult)* | `testpass123` |
| `test_guardian_b` | Angel (20) | *(none - adult)* | `testpass123` |
| `test_minor_junior` | Angel (20) | Junior Angel | `testpass123` |
| `test_minor_teen` | Angel (20) | Teen Angel | `testpass123` |
| `test_minor_child` | Angel (20) | Accompanying Child | `testpass123` |

### User Roles Explained

- **test_shico**: Heaven staff member with shift management permissions
- **test_bureaucrat**: Administrative user for testing admin features
- **test_angel_a/b**: Regular adult angels for baseline testing
- **test_supervisor_a**: Adult willing to supervise minors (trained)
- **test_supervisor_b**: Adult NOT willing to supervise
- **test_guardian_a**: Guardian of test_minor_junior
- **test_guardian_b**: Guardian of test_minor_teen and test_minor_child
- **test_minor_junior**: 13-14 year old (2h/day max, 8-18h, category A only)
- **test_minor_teen**: 15-17 year old (8h/day max, 6-20h, categories A+B)
- **test_minor_child**: Under 13 (no work allowed, guardian accompaniment only)

## Guardian Relationships

| Guardian | Minor | Type | Can Manage Account |
|----------|-------|------|-------------------|
| `test_guardian_a` | `test_minor_junior` | Primary (parent) | Yes |
| `test_guardian_b` | `test_minor_teen` | Primary (parent) | Yes |
| `test_guardian_b` | `test_minor_child` | Primary (parent) | Yes |
| `test_supervisor_a` | `test_minor_teen` | Secondary (supervisor) | No |

## Angel Types

| Name | Work Category | Restricted | Description |
|------|---------------|------------|-------------|
| `test_Infodesk Angel` | A | No | Light work, all volunteers |
| `test_Kidspace Angel` | A | No | Child-friendly area |
| `test_Herald Angel` | B | No | Standard work, teens+ |
| `test_CERT` | B | Yes | Emergency response |
| `test_Bar Angel` | C | No | Adults only |
| `test_Night Security` | C | Yes | Adults only |

### Work Categories

- **Category A**: Light work, suitable for all volunteers including Junior Angels
- **Category B**: Standard work, suitable for Teen Angels and adults
- **Category C**: Adult-only work (alcohol handling, night shifts)

## Locations

| Name | DECT | Description |
|------|------|-------------|
| `test_Heaven Helpdesk` | 1023 | Central coordination |
| `test_Kidspace Hall B` | 1543 | Child-friendly area |
| `test_Main Hall Stage` | 1500 | Primary lecture hall |
| `test_Bar Rubiqs` | 1720 | Adult-only venue |

## Shifts

All shifts use **relative dates** based on when the seeder runs:

| Shift | Type | Location | Day | Time | Supervisor Required |
|-------|------|----------|-----|------|---------------------|
| Infodesk Morning (Past) | Infodesk | Heaven | yesterday | 10:00-12:00 | Yes |
| Infodesk Afternoon (Past) | Infodesk | Heaven | yesterday | 14:00-16:00 | Yes |
| Kidspace Morning | Kidspace | Kidspace | today | 10:00-12:00 | Yes |
| Kidspace Afternoon | Kidspace | Kidspace | today | 14:00-16:00 | Yes |
| Herald Evening | Herald | Main Hall | today | 18:00-20:00 | Yes |
| Junior-Safe Morning | Infodesk | Heaven | tomorrow | 09:00-11:00 | Yes |
| Teen-Extended Evening | Herald | Main Hall | tomorrow | 16:00-20:00 | Yes |
| Too-Early Shift | Infodesk | Heaven | tomorrow | 05:00-07:00 | No |
| Too-Late Shift | Herald | Main Hall | tomorrow | 20:00-22:00 | No |
| Night Bar Shift | Bar | Bar | tomorrow | 22:00-02:00 | No |
| Long Kidspace Day | Kidspace | Kidspace | +2 days | 08:00-18:00 | Yes |
| Overlapping Shift | Infodesk | Heaven | +2 days | 10:00-12:00 | Yes |

## Test Scenarios

### US-04: Shift Signup Validation

| Scenario | User | Expected Result |
|----------|------|-----------------|
| Junior signs up for morning Infodesk | `test_minor_junior` | Success (8-18h, cat A) |
| Junior signs up for evening Herald | `test_minor_junior` | Fail (after 18:00) |
| Junior signs up for category B shift | `test_minor_junior` | Fail (only cat A) |
| Teen signs up for Herald shift | `test_minor_teen` | Success (6-20h, cat A+B) |
| Teen signs up for night shift | `test_minor_teen` | Fail (after 20:00) |
| Teen signs up for Too-Early Shift | `test_minor_teen` | Fail (before 6:00) |
| Teen exceeds 8h/day | `test_minor_teen` | Fail (max 8h) |
| Accompanying Child signup | `test_minor_child` | Fail (0h allowed) |

### US-05: Supervisor Pre-Registration

| Scenario | User | Expected Result |
|----------|------|-----------------|
| Minor without supervisor | Any minor | Fail if shift requires supervisor |
| Minor with guardian supervisor | `test_minor_junior` | Success with `test_guardian_a` |
| Minor with willing supervisor | `test_minor_teen` | Success with `test_supervisor_a` |
| Minor with unwilling supervisor | `test_minor_teen` | Fail with `test_supervisor_b` |

### US-06: Non-Counting Participation

| Scenario | User | Expected Result |
|----------|------|-----------------|
| Minor shift entry | Any minor | Entry has `counts_toward_quota=false` |
| Adult shift entry | Any adult | Entry has `counts_toward_quota=true` |

## CLI Usage

```bash
# Show help
bin/seed-test-data --help

# Seed test data (clears existing first)
bin/seed-test-data

# Only clear test data
bin/seed-test-data --clear

# Quiet mode (no output)
bin/seed-test-data --quiet
```

## File Structure

```
src/Database/Seeders/
  TestDataSeeder.php    # Main seeder class

bin/
  seed-test-data        # CLI entry point

doc/
  test-data.md          # This documentation
```

## Extending the Test Data

To add new test data:

1. Edit `src/Database/Seeders/TestDataSeeder.php`
2. Add new entities in the appropriate `seed*()` method
3. Remember to add cleanup logic in `clear()` method
4. Use the `test_` prefix for all entity names
5. Update this documentation

## Notes

- All test data uses the `test_` prefix for easy identification
- The seeder can be re-run safely - it clears existing test data first
- Dates are relative to today, so tests remain valid over time
- The seeder respects foreign key constraints by creating entities in order
- Test users have all required related records (contact, personal_data, settings, state)
