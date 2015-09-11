class component::oracle_instantclient {
  # don't contain the repository to avoid a dependency cycle
  include apt

  package { 'oracle-instantclient':
    ensure => present,
  }
}
