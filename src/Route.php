<?php

namespace Kzu\Web;

use Kzu\Http\Request;

Trait Route {
    static public $routes = [];

    static public function getRoute(): ?array {
        return Route::getAction(Request::getUri());
    }

    static public function getRouteName(): ?string {
        return Route::getAction(Request::getUri())['name'];
    }

    static public function getRoutePath(?string $route_name = null, ?array $parameters = []): ?string {
        $route = Route::$routes[$route_name ?? Route::getRouteName()] ?? null;
        if (!$route): return null; endif;
        $path = '/';
        foreach (explode('/', $route['path']) as $element):
            if (substr($element, 0, 1) === '{' && substr($element, -1) === '}'):
                $element = str_replace('{', '', $element);
                $element = str_replace('}', '', $element);
                if (key_exists($element, $parameters ?? [])):
                    $element = $parameters[$element];
                    $path .= $element.'/';
                endif;
            elseif ($element):
                $path .= $element.'/';
            endif;
        endforeach;
        return $path ?? null;
    }

    static public function getUrl(?string $route_name, ?array $parameters = []): ?string {
        return Request::getSheme() . '://' . Request::getHost() . Route::getRoutePath($route_name, $parameters);
    }

    /**
     * Get controller & method correlated with $uri
     * @param string $uri
     * @return array|null [$controller, $method, $uri]
     */
    static public function getAction(string $uri): ?array {
        if (strpos($uri, "?")): 
            $uri = substr($uri, 0, strpos($uri, "?"));
        endif;
        foreach (Route::$routes ?? [] as $name => $route):
            if (Route::correlation($uri, $path = $route['path'])):
                if (strpos($controller = $route['controller'], '::') !== false):
                    $route = explode('::', $controller);
                    return [
                        'name' => $name,
                        'class' => $route[0],
                        'method' => $route[1],
                        'path' => $path,
                        'uri' => $uri
                    ];
                endif;
            endif;
        endforeach;
        return null; // Route not found
    }

    /**
     * Check if current $uri === $route
     * @param string $uri
     * @param string $route
     */
    static public function correlation(string $uri, string $route): bool {
        $path_exploded = explode('/', $route) ?? [];
        $uri_exploded = explode('/', $uri) ?? [];
        if (count($path_exploded) === count($uri_exploded) 
        || (end($uri_exploded) === "" && count($path_exploded) === (count($uri_exploded) - 1))):
            foreach ($path_exploded as $index => $value):
                $uri_item = $uri_exploded[$index];
                if ($value !== $uri_item && substr($value, 0, 1) === '{' && substr($value, -1) === '}'):
                    Request::setParameter(ltrim(rtrim($value, '}'), '{'), $uri_item);
                elseif ($value !== $uri_item): return false; endif;
            endforeach;
            return true;
        endif;

        return false;
    }
}