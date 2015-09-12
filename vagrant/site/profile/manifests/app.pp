class profile::app (
  $symfony2        = true,
  $php_vhost       = false,
  $standalone_app  = false,
  $zend_framework1 = false,
  $yii1            = false,
  $yii2            = false,
  $slim            = false,
) {
  validate_bool($symfony2)
  validate_bool($php_vhost)
  validate_bool($standalone_app)
  validate_bool($zend_framework1)
  validate_bool($yii1)
  validate_bool($yii2)
  validate_bool($slim)

  if $symfony2 {
    contain component::symfony2
  }
  if $php_vhost {
    contain component::php_vhost
  }
  if $standalone_app {
    contain component::standalone_app
  }
  if $zend_framework1 {
    contain component::zend_framework1
  }
  if $yii1 {
    contain component::yii1
  }
  if $yii2 {
    contain component::yii2
  }
  if $slim {
    contain component::slim
  }
}
