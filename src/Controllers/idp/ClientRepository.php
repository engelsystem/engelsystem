<?php

namespace Engelsystem\Controllers\idp;

use Engelsystem\Controllers\idp;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /** @var array */
    protected array $clientsConfig;

    /**
     * @param array $clientsConfig
     */
    public function __construct(array $clientsConfig)
    {
        $this->clientsConfig = $clientsConfig;
    }


    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier)
    {
        if (\array_key_exists($clientIdentifier, $this->clientsConfig) == false) {
            return null;
        }

        $clientConfig = $this->clientsConfig[$clientIdentifier];

        $client = $this->clientsConfig[$clientIdentifier];
        $client = new idp\ClientEntity();

        $client->setIdentifier($clientIdentifier);
        $client->setName($clientConfig["name"]);
        $client->setRedirectUri($clientConfig["redirect_uri"]);
        $client->setConfidential($clientConfig["is_confidential"]);

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        if (\array_key_exists($clientIdentifier, $this->clientsConfig) == false) {
            return null;
        }
        if (
            $this->clientsConfig[$clientIdentifier]["is_confidential"] === true && ($clientSecret == null
            || \hash_equals($clientSecret, $this->clientsConfig[$clientIdentifier]['secret']) === false)
        ) {
            return false;
        }

        return true;
    }
}
