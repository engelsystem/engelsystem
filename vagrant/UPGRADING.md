UPGRADING
=========

0.1.0
-----
This adds the possibility to have a whole hiera hierarchy.

You have to make some minor changes due to some differences in loading the files.

### Breaking changes
Add the following to your devstack.yaml which was contained in wasted before
```
# fixes permission errors
php::fpm::user: vagrant
php::fpm::group: users
hhvm::config::user: vagrant
hhvm::config::group: users

# fix fpm <-> hhvm port collision
hhvm::config::port: 9090

# fixes bugs with vboxfs
nginx::sendfile: 'off'
nginx::manage_repo: false

# improve app handling
apt::always_apt_update: true
apt::purge_sources_list: false
apt::purge_sources_list_d: false
apt::purge_preferences_d: false
```

### Advanced folder structure
Instead of the current devstack.yaml you have the possibility to define a whole hiera hierarchy:
```
:hierarchy:
  - vagrant-cfg/local/%{::environment}/%{::fqdn}
  - vagrant-cfg/local/%{::environment}/%{::hostname}
  - vagrant-cfg/local/%{::environment}/common
  - vagrant-cfg/local/%{::fqdn}
  - vagrant-cfg/local/%{::hostname}
  - vagrant-cfg/local/common
  - vagrant-cfg/'%{::environment}/%{::fqdn}'
  - vagrant-cfg/'%{::environment}/%{::hostname}'
  - vagrant-cfg/'%{::environment}/common'
  - vagrant-cfg/'%{::fqdn}'
  - vagrant-cfg/'%{::hostname}'
  - vagrant-cfg/common
```
