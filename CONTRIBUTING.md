# Contributing
## Coding guide lines
* Make sure your code follows the [PSR-12](https://www.php-fig.org/psr/psr-12/) code style and is [.editorconfig](.editorconfig) valid.
  You may use `composer run phpcs` and [Editorconfig-Checker](https://editorconfig-checker.github.io) to verify that.
* Use `use` statements wherever possible instead of writing the fully qualified name.
* Order the composer/npm dependencies alphabetically.
* Do not use code from the [includes](./includes) directory anywhere else.
* Please cover your code by unit tests. Code under `includes` does not require tests.
* Do not use vendor prefixes like `-webkit` in styles.
  This is done by PostCSS + Autoprefixer according to the [`.browserslistrc`](./.browserslistrc).

## Pull requests
Please create single pull requests for every feature instead of creating one big monster of pull request containing a complete rewrite.
