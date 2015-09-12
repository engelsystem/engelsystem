class component::coffee_script {
  require component::nodejs

  package { ['coffee-script', 'jitter']:
    provider => 'npm',
  }
}
