<?php

namespace Kzu\Web;

use Kzu\Web\Flash;
use Kzu\Http\Request;
use Twig\Environment;
use Twig\TwigFunction;
use Kzu\Storage\Session;
use Kzu\Filesystem\Filesystem;
use Twig\Loader\FilesystemLoader;

Trait Twig {
    static public $template_directory;
    static public $cache = true;
    static public $debug = false;
    static public $functions = [
        ["class" => "Kzu\Web\Twig", "method" => "path"],
        ["class" => "Kzu\Web\Twig", "method" => "route"],
        ["class" => "Kzu\Web\Twig", "method" => "session"],
        ["class" => "Kzu\Web\Twig", "method" => "request"],
        ["class" => "Kzu\Web\Twig", "method" => "parameters"],
        ["class" => "Kzu\Web\Twig", "method" => "parameter"],
        ["class" => "Kzu\Web\Twig", "method" => "flash"],
    ];

    static public function view(string $template, ?array $parameters = []) {
        $loader = new FilesystemLoader(Twig::$template_directory);
	if (Twig::$cache !== false):
            Filesystem::mkdir(Twig::$cache);
        endif;
        
        $twig = new Environment($loader, [
            'cache' => Twig::$cache,
            'debug' => Twig::$debug
        ]);

        if (Twig::$debug):
            $twig->addExtension(new \Twig\Extension\DebugExtension());
        endif;

        foreach (Twig::$functions as $function):
            $twig->addFunction(
                $function['class']::{$function['method']}()
            );
        endforeach;

        $template = $twig->load($template);
        return $template->render($parameters);
    }
    
    static public function path() {
        return new TwigFunction('path', function (?string $route_name = null, ?array $parameters = []) {
            return Route::getRoutePath($route_name, $parameters);
        });
    }
    
    static public function route() {
        return new TwigFunction('route', function () {
            return Route::getRoute();
        });
    }

    static public function session() {
        return new TwigFunction('session', function (string $key) {
            return Session::get($key);
        });
    }
    
    static public function request() {
        return new TwigFunction('request', function () {
            return Request::getRequest();
        });
    }

    static public function parameters() {
        return new TwigFunction('parameters', function () {
            return Request::getParameters();
        });
    }

    static public function parameter() {
        return new TwigFunction('parameter', function (string $key) {
            return Request::getParameters($key);
        });
    }

    static public function flash() {
        return new TwigFunction('flash', function (?string $type = null) {
            if ($type): return Flash::get($type);
            else: return Flash::all(); endif;
        });
    }
}
