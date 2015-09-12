class component::less {
  require component::nodejs

  package { 'less':
    provider => npm
  }
}
