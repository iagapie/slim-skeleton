<?php

declare(strict_types=1);

namespace App\Ui\Http\Middleware;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

use function array_map;
use function count;
use function is_array;
use function is_string;

final class AddRoutesMiddleware implements MiddlewareInterface
{
    /**
     * @var App
     */
    private App $app;

    /**
     * @var string
     */
    private string $routesPath;

    /**
     * AddRoutesMiddleware constructor.
     * @param App $app
     * @param string $routesPath
     */
    public function __construct(App $app, string $routesPath)
    {
        $this->app = $app;
        $this->routesPath = $routesPath;
    }

    /**
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $routes = require $this->routesPath;
        $this->add($this->app, $routes);

        return $handler->handle($request);
    }

    /**
     * @param RouteCollectorProxyInterface $routeCollectorProxy
     * @param array $items
     */
    private function add(RouteCollectorProxyInterface $routeCollectorProxy, array $items): void
    {
        foreach ($items as $item) {
            if (!is_array($item) || ($size = count($item)) < 2) {
                throw new InvalidArgumentException('Route item is not valid.');
            }

            if (is_string($item[0]) && is_array($item[1])) {
                $data = $item[1];
                $routeGroup = $routeCollectorProxy->group(
                    $item[0],
                    fn(RouteCollectorProxyInterface $group) => $this->get(self::class)->add($group, $data)
                );

                if (isset($item[2])) {
                    $routeGroup->add($item[2]);
                }

                continue;
            }

            if ($size < 3) {
                throw new InvalidArgumentException('Route item is not valid.');
            }

            list($methods, $pattern, $callable) = $item;

            $methods = array_map('strtoupper', (array)$methods);

            $route = $routeCollectorProxy->map($methods, $pattern, $callable);

            if (isset($item[3])) {
                $route->setName($item[3]);
            }

            if (isset($item[4])) {
                $route->add($item[4]);
            }
        }
    }
}
