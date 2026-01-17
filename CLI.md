# Engelsystem CLI

The `bin/engel` script manages Engelsystem from the command line.

For usage see `./bin/engel list`

## Global Options

- `--json` - Output as JSON
- `-h, --help` - Show help
- `-q, --quiet` - Suppress output
- `-v` - Verbose output

## Commands

### User Management

#### user:create

Create a new user.

```bash
bin/engel user:create <nick> [options]
```

Options:
- `-P, --password <pass>` - Set password (auto-generated if omitted)
- `-e, --email <email>` - Email address
- `-g, --groups <names>` - Comma-separated group names (e.g., "Angel,Shift Coordinator")
- `-A, --angeltypes <names>` - Comma-separated angel types to join

Examples:
```bash
# Create user with password and groups
bin/engel user:create alice -P secret123 -g "Angel,Shift Coordinator"

# Create user with auto-generated password
bin/engel user:create bob -e bob@example.com
```

#### user:list

List users with optional filters.

```bash
bin/engel user:list [options]
```

Options:
- `-g, --group <name>` - Filter by group name
- `-a, --angeltype <name>` - Filter by angel type
- `-l, --limit <n>` - Limit results (default: 50)

Examples:
```bash
# List all users as JSON
bin/engel user:list --json

# List users in a specific group
bin/engel user:list -g "Shift Coordinator"
```

#### user:show

Show details for a specific user.

```bash
bin/engel user:show <nick|id>
```

### Angel Type Management

#### angeltype:list

List all angel types with member counts.

```bash
bin/engel angeltype:list
```

#### angeltype:create

Create a new angel type.

```bash
bin/engel angeltype:create <name> [options]
```

Options:
- `-d, --description <text>` - Description
- `-r, --restricted` - Require confirmation for membership
- `--hidden` - Hide from registration page

### Location Management

#### location:list

List all locations.

```bash
bin/engel location:list
```

#### location:create

Create a new location.

```bash
bin/engel location:create <name> [options]
```

Options:
- `-d, --description <text>` - Description
- `--dect <number>` - DECT number
- `--map-url <url>` - Map URL

### Group Management

#### group:list

List all groups with member and privilege counts.

```bash
bin/engel group:list
```

#### group:add-user

Add a user to a group.

```bash
bin/engel group:add-user <group> <user>
```

Arguments can be names or IDs.

### Shift Management

#### shift:list

List shifts with filters.

```bash
bin/engel shift:list [options]
```

Options:
- `-l, --location <name>` - Filter by location name
- `-t, --type <name>` - Filter by shift type
- `-d, --date <Y-m-d>` - Filter by date
- `-u, --upcoming` - Show only upcoming shifts
- `--limit <n>` - Limit results (default: 50)

#### shift:signup

Sign up a user for a shift.

```bash
bin/engel shift:signup <shift_id> <user> <angeltype> [options]
```

Options:
- `-c, --comment <text>` - User comment
- `-f, --force` - Force signup even if shift is full or user is not a member of the angel type

### Configuration

#### config:list

List all event configuration values from the database.

```bash
bin/engel config:list
```

#### config:get

Get a configuration value.

```bash
bin/engel config:get <key>
```

#### config:set

Set a configuration value.

```bash
bin/engel config:set <key> <value> [options]
```

Options:
- `-t, --type <type>` - Value type: string (default), int, bool, json

Examples:
```bash
# Set a string value
bin/engel config:set welcome_message "Hello angels!"

# Set a boolean value
bin/engel config:set enable_feature true -t bool

# Set a JSON value
bin/engel config:set my_list '["a","b","c"]' -t json
```

### Database

#### db:migrate

Run database migrations.

```bash
bin/engel db:migrate [options]
```

Options:
- `-d, --down` - Rollback migrations
- `--one-step` - Only run one migration
- `-f, --force` - Force migration even if locked
- `-p, --prune` - Prune all database tables before run

#### db:seed

Seed database with test data.

```bash
bin/engel db:seed [options]
```

Options:
- `-l, --locations <n>` - Number of locations to create (default: 10)
- `-d, --days <n>` - Number of days of shifts (default: 3)
- `-s, --shifts-per-day <n>` - Shifts per day per location (default: 4)

## Scripting Examples

### Export all users as JSON

```bash
bin/engel user:list --json > users.json
```

### Create multiple users from a file

```bash
while IFS=, read -r nick email; do
  bin/engel user:create "$nick" -e "$email" -g Angel
done < users.csv
```

### Check if a user exists

```bash
if bin/engel user:show alice --json > /dev/null 2>&1; then
  echo "User exists"
else
  echo "User not found"
fi
```
