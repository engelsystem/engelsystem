class profile::custom_hosts (
    $hosts = {}
) {
    validate_hash($hosts)
    create_resources(host, $hosts)
}
