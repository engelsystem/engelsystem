class profile::packages (
  $names = [],
) {
  ensure_packages($names, {
    ensure => present,
  })
}
