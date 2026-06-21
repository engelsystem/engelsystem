# Contributing

## Talk to us first

Please discuss your intended changes with us in an issue before opening a pull request. This helps both of us to find the right solution for the problem you want to solve or the feature you like to be added to the Engelsystem.

The only exception are little bug fixes that only change a few lines of code. You can open a pull request directly without prior discussion if this is the case.

## Code Style

* Your code must follow the the [PSR-12](https://www.php-fig.org/psr/psr-12/) code style
* You should use an [editorconfig plugin for your editor](https://editorconfig.org/#pre-installed) for automatic basic code formatting.
  * It must be [.editorconfig](.editorconfig) valid
* You may use `composer run phpcs` and [Editorconfig-Checker](https://editorconfig-checker.github.io) to verify your code style.
* Use `use` statements wherever possible instead of writing the fully qualified name.
* Do not use code from the [includes](includes) directory anywhere else.
* Order the composer/npm dependencies alphabetically.
* Do not use vendor prefixes like `-webkit` in styles.
  * This is done by PostCSS + Autoprefixer according to the [`.browserslistrc`](.browserslistrc).
* Don't put function calls in a template-literal (template-strings).

## Quality and Tests

* Code must pass PHPStan checks (`composer phpstan`)
* Please cover your code by unit tests, our goal is to stay at 100% line coverage.
  * Code under `includes` does not require tests as it's mostly not testable and needs to be rewritten/replaced.
* JavaScript code must pass the checks `yarn lint`.
  * Auto-fixing is supported via `yarn lint:fix`.

## Legacy

* Don't refactor [includes](includes) code just for the sake of change, it is legacy code that must only be replaced.

## i18n

* Translations must be abbreviated, for example `form.save` and should be reused if possible.
  The `default.po` files contain translations that can be auto-detected using Poedit, `additional.po` contains generated messages like validations.

## AI/LLM Policy

> The engelsystem will not accept AI/LLM-generated pull requests. We consider a PR as AI/LLM-generated when it consists partly of generated code or documentation.

* **All AI/LLM Usage must be disclosed**. This includes the tool(s) used for creating the code
  * The disclosure demand includes texts in issues and pull request descriptions
* Every AI written code (and text) must be fully reviewed and tested by a human (you) and not by another AI.
* Code and documentation must not look like it was generated.
* You have to manually verify the copyright and compliance status of your contribution. AI code can contain copyright claimed or licenced code which is not compatible to the Engelsystem [Licence](./LICENSE).
* Media (Icons etc.) created by an AI/LLM ist generally not allowed.

> If you do not apply the this rules your issues and pull requests will be closed and you risk to be banned permanently. You also risk to be rediculed in public.

## Pull Requests

Like said before "*Talk to us first*"!

Please submit small PRs. We are a small team doing this in our spare time and do not have the time nor the energy to review and test a 1000 line PR in 400 files.

* Create one PR per feature
* Squash similar commits to make the review easier
  * Best is one commit per feature

## Commits

* Use [Conventional Changelog](https://github.com/TrigenSoftware/simple-release/blob/main/GUIDE.md) alike Commit messages.
* The commit title should not exceed 120 characters.
* You can and should add additional commit context in the commit body.
* The commit message must be meaningful and describe the work done.

### Example

```shell
git commit -m "feat(shifts): add some cool feature to the shift search" -m "Body text row 1 - Dscription of the cool feature" -m "Body text row 2 - Further description"
-m "Body text row 3 - Maybe the regarding issue"
```

## General Exceptions

If you are a well known maintainer or contributor some of this rules might not apply as strong as they are to new contributors. Maintainers and well known contributors are strongly encouraged to apply to all this rules.