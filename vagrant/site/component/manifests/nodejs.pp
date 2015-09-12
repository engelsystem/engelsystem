class component::nodejs {
  class { '::nodejs':
    version      => 'stable',
    make_install => false
  }
  contain '::nodejs'
}
