class profile::sync (
  $files = {},
  $directories = {}
) {

  validate_hash($files)
  create_resources('file', $files, {
    ensure => 'present',
    owner => 'vagrant',
    group => 'vagrant',
  })

  validate_hash($directories)
  create_resources('file', $directories, {
    recurse => true,
    ensure => 'directory',
    owner => 'vagrant',
    group => 'vagrant',
  })
}
