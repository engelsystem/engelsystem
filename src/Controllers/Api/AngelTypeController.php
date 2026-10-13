<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\AngelTypeResource;
use Engelsystem\Controllers\Api\Resources\UserAngelTypeResource;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;

class AngelTypeController extends ApiController
{
    use UsesAuth;

    /**
     * Readable with the "angeltypes.view" privilege, same as the angel type pages on the website.
     * A user's angel types are shown to everyone on their profile page.
     */
    protected function webPermission(string $method): string|array|bool|null
    {
        return match ($method) {
            'index', 'show' => 'angeltypes.view',
            'ofUser' => true,
            default => null,
        };
    }

    public function index(): Response
    {
        $models = AngelType::query()
            ->orderBy('name')
            ->get();

        $data = ['data' => AngelTypeResource::collection($models)];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('angeltype_id');
        /** @var AngelType $angelType */
        $angelType = AngelType::findOrFail($id);

        $data = ['data' => (new AngelTypeResource($angelType))->toArray()];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function ofUser(Request $request): Response
    {
        $id = $request->getAttribute('user_id');
        $user = $this->getUser($id);

        $data = ['data' => UserAngelTypeResource::collection($user->userAngelTypes)];

        return $this->response
            ->withContent(json_encode($data));
    }
}
