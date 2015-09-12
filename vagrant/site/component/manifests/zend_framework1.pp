class component::zend_framework1 (
  $path       = hiera('path', '/var/www/app_name'),
  $vhost      = hiera('vhost', 'app-name.dev'),
  $vhost_port = 80,
  $env        = hiera('env', 'dev'),
) {

  nginx::resource::vhost { "${vhost}-${vhost_port}-zend_framework1":
    server_name         => [$vhost],
    listen_port         => $vhost_port,
    www_root            => "${path}/public",
    fastcgi             => '127.0.0.1:9000',
    location_cfg_append => {
      fastcgi_index => 'index.php',
      fastcgi_param => [
        'SCRIPT_FILENAME $document_root/index.php',
        "APPLICATION_ENV ${env}"
      ]
    },
  }

  nginx::resource::location{ "${vhost}_static":
    location  => '~ ^/(style|images|scripts)/',
    vhost     => "${vhost}-${vhost_port}-zend_framework1",
    www_root  => "${path}/public",
    try_files => ['$uri', '=404']
  }

  if defined(Class['::hhvm']) {
    nginx::resource::vhost { "hhvm.${vhost}-${vhost_port}-zend_framework1":
      server_name         => ["hhvm.${vhost}"],
      listen_port         => $vhost_port,
      www_root            => "${path}/public",
      fastcgi             => '127.0.0.1:9090',
      location_cfg_append => {
        fastcgi_index => 'index.php',
        fastcgi_param => [
          'SCRIPT_FILENAME $document_root/index.php',
          "APPLICATION_ENV ${env}"
        ]
      },
    }

    nginx::resource::location{ "hhvm.${vhost}_static":
      location  => '~ ^/(style|images|scripts)/',
      vhost     => "hhvm.${vhost}-${vhost_port}-zend_framework1",
      www_root  => "${path}/public",
      try_files => ['$uri', '=404']
    }
  }
}
