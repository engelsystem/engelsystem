class component::mysql (
  $root_password = undef,
  $databases = {}
) {
  validate_hash($databases)

  if $root_password != undef {
    fail('Setting the MySQL root password with component::mysql::root_password is not supported anymore. Please use mysql::server::root_password instead!')
  }

  contain '::mysql::server'
  contain '::mysql::client'

  create_resources('::mysql::db', $databases)
}
