## Steps to run database migration

#### Installing Phinx
 To install Phinx, simply require it using Composer:
- $ php composer.phar require robmorgan/phinx

Then run Composer:
- $ php composer.phar install --no-dev

Create a folder in your project directory called migrations with adequate permissions. It is where your migration files will live and should be writable.
- $ php vendor/bin/phinx init

#### Creating a New Migration
- $ php vendor/bin/phinx create MyNewMigration

This will create a new migration in the format YYYYMMDDHHMMSS_my_new_migration.php where the first 14 characters are replaced with the current timestamp down to the second.

#### The Migrate Command

The Migrate command runs all of the available migrations, optionally up to a specific version.
- $ phinx migrate -e development

To migrate to a specific version then use the --target parameter or -t for short.
- $ phinx migrate -e development -t 20110103081132

#### The Rollback Command

You can rollback to the previous migration by using the rollback command with no arguments.
- $ phinx rollback -e development

#### The Status Command

The Status command prints a list of all migrations, along with their current status. You can use this command to determine which migrations have been run.
- $ phinx status -e development

#### Writing Migrations
More information on writing migrations is available here:
-  http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class