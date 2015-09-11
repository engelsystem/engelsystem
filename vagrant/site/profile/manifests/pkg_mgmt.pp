class profile::pkg_mgmt (
  $yum_repos = {},
) {
  if $::osfamily == 'Debian' {
    contain ::apt

  } elsif $::osfamily == 'RedHat' {
    contain ::yum

    create_resources(::yum::managed_yumrepo, $yum_repos, {
      enabled  => 1,
      priority => 1,
    })
  }
}
