class component::standalone_app (
  $path       = hiera('path', '/var/www/app_name'),
  $vhost      = hiera('vhost', 'app-name.dev'),
  $vhost_port = 80,
  $port       = 5000,
  $prefix     = 'api'
) {

  nginx::resource::vhost { "${vhost}-${vhost_port}-standalone":
    server_name => [$vhost],
    www_root    => $path,
    listen_port => $vhost_port,
    index_files => ['index.html'],
    try_files   => ['$uri', '$uri/', '/index.html', '=404'],
  }

  nginx::resource::upstream { 'standalone_app':
    members => [
      "localhost:${port}",
    ],
  }

  nginx::resource::location { "/${prefix}":
    vhost => "${vhost}-${vhost_port}-standalone",
    proxy => 'http://standalone_app',
  }
}
