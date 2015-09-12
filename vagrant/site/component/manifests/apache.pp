class component::apache (
  $mods = [],
  $vhosts = {}
) {
  validate_array($mods)
  validate_hash($vhosts)

  contain ::apache

  ::apache::default_mods::load { $mods: }

  create_resources(::apache::vhost, $vhosts)
}
