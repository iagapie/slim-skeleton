<?php

declare(strict_types=1);

use App\Ui\Http\Rest\Controller\HelloController as ApiHelloController;
use App\Ui\Http\Rest\Controller\OpenApiController;
use App\Ui\Http\Web\Controller\HelloController;
use App\Ui\Http\Web\Controller\SwaggerController;

return [
    ['GET', '/', HelloController::class],
    [
        '/api/v{version:[1]}',
        [
            ['GET', '/openapi', SwaggerController::class.':getOpenApi'],
            ['GET', '/openapi.json', OpenApiController::class.':getOpenApiJson'],
            ['GET', '/openapi.yaml', OpenApiController::class.':getOpenApiYaml'],

            [['POST'], '/hello', ApiHelloController::class.':world'],
        ],
    ],
];
