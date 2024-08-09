<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Test\Unit\Controllers\ControllerTest as TestCase;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress as OpenApiAddress;
use League\OpenAPIValidation\PSR7\ResponseValidator as OpenApiResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder as OpenApiValidatorBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;

abstract class ApiBaseControllerTest extends TestCase
{
    protected OpenApiResponseValidator $validator;

    protected function validateApiResponse(string $path, string $method, ResponseInterface $response): void
    {
        $operation = new OpenApiAddress($path, $method);
        try {
            $this->validator->validate($operation, $response);
        } catch (InvalidBody $e) {
            $newMessage = $e->getMessage() . ': ' . $e->getPrevious()?->getMessage();
            throw new ValidationFailed($newMessage, $e->getCode(), $e);
        }
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $openApiDefinition = $this->app->get('path.resources.api') . '/openapi.yml';
        $this->validator = (new OpenApiValidatorBuilder())
            ->fromYamlFile($openApiDefinition)
            ->getResponseValidator();

        /** @var UrlGeneratorInterface|MockObject $url */
        $url = $this->getMockForAbstractClass(UrlGeneratorInterface::class);
        $url->expects($this->any())
            ->method('to')
            ->willReturnCallback(function (string $path, array $params): string {
                $query = http_build_query($params);
                return $path . ($query ? '?' . $query : '');
            });
        $this->app->instance('http.urlGenerator', $url);
    }
}
