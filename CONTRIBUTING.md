# Contributing
## Coding guide lines
* Make sure your code follows the [PSR-12](https://www.php-fig.org/psr/psr-12/) code style and is [.editorconfig](.editorconfig) valid.
  You may use `composer run phpcs` and [Editorconfig-Checker](https://editorconfig-checker.github.io) to verify that.
* You should use an [editorconfig plugin for your editor](https://editorconfig.org/#pre-installed) for automatic basic code formatting.
* Use `use` statements wherever possible instead of writing the fully qualified name.
* Code must pass PHPStan checks (`composer phpstan`)
* Order the composer/npm dependencies alphabetically.
* Do not use code from the [includes](includes) directory anywhere else.
* Please cover your code by unit tests, our goal is to stay at 100% line coverage.
  Code under `includes` does not require tests as it's mostly not testable and needs to be rewritten.
* Do not use vendor prefixes like `-webkit` in styles.
  This is done by PostCSS + Autoprefixer according to the [`.browserslistrc`](.browserslistrc).
* Translations must be abbreviated, for example `form.save`.
  The `default.po` files contain translations that can be auto-detected using Poedit, `additional.po` contains generated messages like validations.
* JavaScript code must pass the checks `yarn lint`.
  Auto-fixing is supported via `yarn lint:fix`.
* Don't put function calls in a template-literal (template-strings).

## Pull requests
* The PR should contain a short overview of the changes.
* Before implementing bigger changes, please open an issue to discuss the feature and possible implementation options.
* Please create single pull requests for every feature instead of creating one big monster of pull request containing a complete rewrite.
* Squash similar commits to make the review easier.
* For visual changes, include both before and after screenshots to easily compare and discuss changes.

## Commits
* The commit message must be meaningful. It should serve as a short overview of the changes.
  If needed, an additional description can be provided.
* A commit should be self-contained and result in a working Engelsystem.
