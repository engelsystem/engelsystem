class component::bower {
  require component::nodejs

  package { 'bower':
    provider => 'npm',
  }
}
