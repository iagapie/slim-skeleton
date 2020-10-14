<?php

declare(strict_types=1);

namespace App\Ui\Http\Middleware;

use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteResolverInterface;

final class RoutingMiddleware extends \Slim\Middleware\RoutingMiddleware
{
    /**
     * RoutingMiddleware constructor.
     * @param RouteResolverInterface $routeResolver
     * @param RouteCollectorInterface $routeCollector
     */
    public function __construct(RouteResolverInterface $routeResolver, RouteCollectorInterface $routeCollector)
    {
        parent::__construct($routeResolver, $routeCollector->getRouteParser());
    }
}
