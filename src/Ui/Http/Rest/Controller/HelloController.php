<?php

declare(strict_types=1);

namespace App\Ui\Http\Rest\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class HelloController
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function world(Request $request, Response $response): Response
    {
        $response->getBody()->write(\json_encode(['success' => true, 'message' => 'API Hello World!!!']));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
