node default {

  Exec {
    path => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin'
  }

  if $::bootstrapped == undef {
    exec { 'apt-update':
      command => 'apt-get update',
      unless  => 'which git',
      before => Package['ruby', 'git', 'r10k']
    } ->
    file { ['/etc/facter', '/etc/facter/facts.d/']:
      ensure  => directory,
      recurse => true,
    } ->
    file { '/etc/facter/facts.d/bootstrapped.txt':
      ensure  => file,
      content => "bootstrapped=true\n",
    }

    augeas { 'remove-deprecated-templatedir-parameter':
      context => '/files/etc/puppet/puppet.conf/main',
      changes => [
        'rm templatedir',
      ],
    }
  }

  package { ['ruby', 'git']:
    ensure        => installed,
    allow_virtual => true,
  } ->

  package { ['deep_merge', 'r10k']:
    ensure   => installed,
    provider => 'gem',
  } ->

  exec { 'r10k-puppetfile-install':
    command => 'r10k -v info puppetfile install && touch modules/.r10k_stamp',
    cwd     => '/vagrant/vagrant',
    onlyif  => 'test ! -e modules/.r10k_stamp || test modules/.r10k_stamp -ot Puppetfile',
  }
}
