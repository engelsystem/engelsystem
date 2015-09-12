class component::yii2 (
  $path  = hiera('path', '/var/www/app_name'),
  $vhost = hiera('vhost', 'app-name.dev'),
  $env   = hiera('env', 'dev'),
) {

  nginx::resource::vhost { $vhost:
    www_root            => "${path}/web",
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
    location  => '~ ^/(assets|css|images|js)/',
    vhost     => $vhost,
    www_root  => "${path}/web",
    try_files => ['$uri', '=404']
  }
}
