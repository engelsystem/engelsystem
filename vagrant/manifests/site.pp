class wasted {
  contain profile::packages
  contain profile::database
  contain profile::queue
  contain profile::javascript
  contain profile::frontend
  contain profile::interpreter
  contain profile::webserver
  contain profile::app
}

node default {
  Package {
    allow_virtual => true,
  }

  class { 'profile::sync': }         ->
  class { 'profile::custom_hosts': } ->
  class { 'profile::pkg_mgmt': }     ->
  class { 'wasted': }

}
