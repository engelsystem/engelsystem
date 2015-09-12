class component::grunt {
  require component::nodejs

  package { 'grunt-cli':
    provider => 'npm'
  }
}
