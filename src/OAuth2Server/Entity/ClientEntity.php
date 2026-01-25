<?php

declare(strict_types=1);

namespace Engelsystem\OAuth2Server\Entity;

use Engelsystem\Models\OAuth2Client;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ClientEntity implements ClientEntityInterface
{
    use ClientTrait;
    use EntityTrait;

    public function __construct(protected OAuth2Client $model)
    {
        $this->identifier = $model->identifier;
        $this->name = $model->name;
        $this->redirectUri = $model->redirect_uris;
        $this->isConfidential = $model->confidential;
    }

    public function getModel(): OAuth2Client
    {
        return $this->model;
    }
}
