<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use cebe\openapi\spec\OpenApi;
use Engelsystem\Http\Response;
use League\OpenAPIValidation\PSR7\ValidatorBuilder as OpenApiValidatorBuilder;

class IndexController extends ApiController
{
    public array $permissions = [
        'index' => 'api',
        'indexV0' => 'api',
        'openApiV0' => 'api',
    ];

    public function index(): Response
    {
        return $this->response
            ->withContent(json_encode([
                'versions' => [
                    $this->getApiSpecV0()->info->version => '/v0-beta',
                ],
            ]));
    }

    public function indexV0(): Response
    {
        $schema = $this->getApiSpecV0();
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

    public function openApiV0(): Response
    {
        $schema = $this->getApiSpecV0();
        $data = $schema->getSerializableData();
        unset($data->servers[1]);
        $data->servers[0]->url = url('/api/v0-beta');

        return $this->response->withContent(json_encode($data));
    }

    public function info(): Response
    {
        $config = config();
        $schema = $this->getApiSpecV0();

        $data = ['data' => [
            'api' => $schema->info->version,
            'spec' => url('/api/v0-beta/openapi'),
            'name' => (string) $config->get('name'),
            'app_name' => (string) $config->get('app_name'),
            'url' => url('/'),
            'timezone' => (string) $config->get('timezone'),
            'buildup' => [
                'start' => $config->get('buildup_start'),
            ],
            'event' => [
                'start' => $config->get('event_start'),
                'end' => $config->get('event_end'),
            ],
            'teardown' => [
                'end' => $config->get('teardown_end'),
            ],
        ]];

        return $this->response
            ->withContent(json_encode($data));
    }

    public function options(): Response
    {
        // Respond to browser preflight options requests
        return $this->response
            ->setStatusCode(200)
            ->withHeader('allow', 'OPTIONS, HEAD, GET')
            ->withHeader('access-control-allow-headers', 'Authorization, x-api-key');
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

    protected function getApiSpecV0(): OpenApi
    {
        $openApiDefinition = app()->get('path.resources.api') . '/openapi.yml';
        return (new OpenApiValidatorBuilder())
            ->fromYamlFile($openApiDefinition)
            ->getResponseValidator()
            ->getSchema();
    }
}
