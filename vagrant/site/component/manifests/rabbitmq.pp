class component::rabbitmq (
  $users = {},
  $vhosts = {},
  $userpermissions = {}
) {
  validate_hash($vhosts)
  validate_hash($users)
  validate_hash($userpermissions)

  anchor { 'component::rabbitmq::begin': } ->
    class { '::rabbitmq': } ->
  anchor { 'component::rabbitmq::end': }

  create_resources('rabbitmq_user', $users, {
    require => Anchor['component::rabbitmq::begin'],
    before  => Anchor['component::rabbitmq::end']
  })

  create_resources('rabbitmq_vhost', $vhosts, {
    require => Anchor['component::rabbitmq::begin'],
    before  => Anchor['component::rabbitmq::end']
  })

  create_resources('rabbitmq_user_permissions', $userpermissions, {
    require => Anchor['component::rabbitmq::begin'],
    before  => Anchor['component::rabbitmq::end']
  })
}
