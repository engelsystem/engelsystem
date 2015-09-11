class component::nginx (
  $vhosts = {},
  $locations = {},
) {

  validate_hash($vhosts)
  validate_hash($locations)

  contain '::nginx'

  create_resources('::nginx::resource::vhost', $vhosts)
  create_resources('::nginx::resource::location', $locations)
}
