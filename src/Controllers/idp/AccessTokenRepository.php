<?php

namespace Engelsystem\Controllers\idp;

use Engelsystem\Database\Db;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        // Some logic here to save the access token to a database
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId)
    {
        // Some logic here to revoke the access token
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId)
    {
        return false; // Access token hasn't been revoked
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        $accessToken->setUserIdentifier($userIdentifier);

        $groupEntities = DB::select('
                SELECT `Groups`.`name`
                FROM `users`
                JOIN `UserGroups` ON (`users`.`id` = `UserGroups`.`uid`)
                JOIN `Groups` ON (`UserGroups`.`group_id` = `Groups`.`UID`)
                WHERE `users`.`id`=?
            ', [$userIdentifier]);

        $groups = [];
        foreach ($groupEntities as $groupEntity) {
            $groups[] = $groupEntity['name'];
        }

        $accessToken->setGroups($groups);

        return $accessToken;
    }
}
