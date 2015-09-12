class profile::queue (
  $rabbitmq = false,
) {
  validate_bool($rabbitmq)

  if $rabbitmq {
    contain component::rabbitmq
  }
}
