<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Http\Response;
use League\OpenAPIValidation\PSR7\ValidatorBuilder as OpenApiValidatorBuilder;

class IndexController extends ApiController
{
    public array $permissions = [
        'index' => 'api',
        'indexV0' => 'api',
    ];

    public function index(): Response
    {
        return $this->response
            ->withContent(json_encode([
                'versions' => [
                    '0.0.1-beta' => '/v0-beta',
                ],
            ]));
    }

    public function indexV0(): Response
    {
        $openApiDefinition = app()->get('path.resources.api') . '/openapi.yml';
        $schema = (new OpenApiValidatorBuilder())
            ->fromYamlFile($openApiDefinition)
            ->getResponseValidator()
            ->getSchema();
        $info = $schema->info;
        $paths = [];
        foreach ($schema->paths->getIterator() as $path => $item) {
            $paths[] = $path;
        }

        return $this->response
            ->withContent(json_encode([
                'version' => $info->version,
                'description' => $info->description,
                'paths' => $paths,
            ]));
    }

    public function options(): Response
    {
        // Respond to browser preflight options requests
        return $this->response
            ->setStatusCode(200)
            ->withHeader('allow', 'OPTIONS, HEAD, GET')
            ->withHeader('access-control-allow-headers', 'Authorization');
    }

    public function notFound(): Response
    {
        return $this->response
            ->setStatusCode(404)
            ->withContent(json_encode(['message' => 'Not implemented']));
    }

    public function notImplemented(): Response
    {
        return $this->response
            ->setStatusCode(405)
            ->withHeader('allow', 'GET')
            ->withContent(json_encode(['message' => 'Method not implemented']));
    }
}
