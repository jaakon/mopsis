<?php
namespace Mopsis\Support;

use Mopsis\Core\App;

class MiscHelpers
{
    public static function between($value, $min, $max)
    {
        if (!ctype_digit($min)) {
            $min = -1 * \PHP_INT_MAX;
        }

        if (!ctype_digit($max)) {
            $max = \PHP_INT_MAX;
        }

        return min(max($value, $min), $max);
    }

    public static function debug(...$args)
    {
        if (DEBUG && count($args)) {
            dump(...$args);
        }

        return $args;
    }

    public static function getStaticPage($code)
    {
        $pages = App::get('static-pages');

        if (isset($pages[$code])) {
            return file_get_contents($pages[$code]);
        }

        $baseCode = strval(floor($code / 100) * 100);

        if (isset($pages[$baseCode])) {
            return file_get_contents($pages[$baseCode]);
        }

        throw new \Exception('static-page ' . $code);
    }

    public static function locationChange($uri, $code = 302, $phrase = null)
    {
        $response = App::get('Aura\Web\Response');

        $response->status->set($code, $phrase);
        $response->content->set(json_encode(['location' => PathHelpers::addLocation($uri)]));

        return $response;
    }

    public static function logger($message)
    {
        if ($message === null) {
            return App::make('Logger');
        }

        return App::make('Logger')->addNotice($message);
    }

    public static function redirect($uri, $code = 302, $phrase = null)
    {
        $response = App::get('Aura\Web\Response');

        $response->redirect->to(PathHelpers::addLocation($uri), $code, $phrase);

        return $response;
    }

    public static function stop(...$args)
    {
        if (!DEBUG) {
            return;
        }

        if (count($args)) {
            dump(...$args);
        }

        $stack    = debug_backtrace(null, 3);
        $location = $stack[1]['file'] . ':' . $stack[1]['line'];
        $link     = 'subl://open?url=file://' . urlencode($stack[1]['file']) . '&line=' . $stack[1]['line'];

        exit('Stopped at [<a href="' . $link . '">' . $location . '</a>]');
    }
}
