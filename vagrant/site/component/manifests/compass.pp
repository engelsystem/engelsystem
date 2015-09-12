class component::compass {
  ensure_packages(['ruby-dev', 'build-essential'])

  package { 'compass':
    provider => 'gem',
    require  => [
      Package['ruby-dev'],
      Package['build-essential']
    ]
  }
}
