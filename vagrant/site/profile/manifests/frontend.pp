class profile::frontend (
  $compass       = false,
  $bower         = false,
  $coffee_script = false,
  $grunt         = false
) {
  validate_bool($compass)
  validate_bool($bower)
  validate_bool($grunt)
  validate_bool($coffee_script)

  if $compass {
    contain component::compass
  }

  if $bower {
    contain component::bower
  }

  if $coffee_script {
    contain component::coffee_script
  }

  if $grunt {
    contain component::grunt
  }
}
