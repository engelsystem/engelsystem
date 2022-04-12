<?php

namespace Engelsystem\Controllers\idp;

use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AccessTokenEntity implements AccessTokenEntityInterface
{
    use TokenEntityTrait, EntityTrait;

    /** @var array */
    protected $groups;

    /** @var CryptKey */
    private  $privateKey;

    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function setPrivateKey(CryptKey $privateKey)
    {
        $this->privateKey = $privateKey;
    }

    /**
     * Generate a JWT from the access token
     *
     * @return Token
     */
    public function convertToJWT() {
        $jwtConfiguration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->privateKey->getKeyContents(), $this->privateKey->getPassPhrase() ?? ''),
            InMemory::plainText('')
        );

        return $jwtConfiguration->builder()
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo((string)$this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes())
            ->withClaim('groups', $this->getGroups())
            ->getToken($jwtConfiguration->signer(), $jwtConfiguration->signingKey());
    }

    /**
     * Generate a string representation from the access token
     */
    public function __toString()
    {
        return $this->convertToJWT()->toString();
    }
}
