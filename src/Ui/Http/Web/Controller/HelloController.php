<?php

declare(strict_types=1);

namespace App\Ui\Http\Web\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class HelloController
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function __invoke(Request $request, Response $response): Response
    {
        $response->getBody()->write('<!DOCTYPE html><html lang="en"><body><p>Hello World!!!</p></body></html>');

        return $response
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
}
