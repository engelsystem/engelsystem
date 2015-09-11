class profile::javascript (
  $nodejs = false,
  $less   = false
) {
  validate_bool($nodejs)
  validate_bool($less)

  if $nodejs {
    contain component::nodejs
  }

  if $less {
    contain component::less
  }
}
