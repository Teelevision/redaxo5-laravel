<?php

namespace App\Routing;

use App\Foundation\Application;

class RoutingServiceProvider extends \Illuminate\Routing\RoutingServiceProvider
{

    protected function registerRouter()
    {
        $this->app->singleton('router', function (Application $app) {
            return new Router($app['events'], $app);
        });
    }

    protected function registerUrlGenerator()
    {
        $this->app->singleton('url', function (Application $app) {
            $routes = $app['router']->getRoutes();

            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $app->instance('routes', $routes);

            $url = new UrlGenerator(
                $routes, $app->rebinding('request', $this->requestRebinder())
            );

            $url->setSessionResolver(function () {
                return $this->app['session'];
            });

            // If the route collection is "rebound", for example, when the routes stay
            // cached for the application, we will need to rebind the routes on the
            // URL generator instance so it has the latest version of the routes.
            $app->rebinding('routes', function (Application $app, $routes) {
                $app['url']->setRoutes($routes);
            });

            return $url;
        });
    }
}