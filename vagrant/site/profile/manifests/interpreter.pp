class profile::interpreter (
  $php  = true,
  $hhvm = false,
) {
  validate_bool($php)
  validate_bool($hhvm)

  if $php {
    contain component::php
  }
  if $hhvm {
    contain component::hhvm
  }
}
