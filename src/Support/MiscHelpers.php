<?php namespace Mopsis\Support;

use Ladybug\Dumper;
use Mopsis\Core\App;

class MiscHelpers
{
	public static function debug(...$args)
	{
		$ladybug = new Dumper();

		$ladybug->setTheme('modern');
		$ladybug->setOption('expanded', false);
		$ladybug->setOption('helpers', ['debug']);

		echo $ladybug->dump(...$args);
	}

	public static function getStaticPage($code)
	{
		$pages = App::get('static-pages');

		if (isset($pages[$code])) {
			return file_get_contents($pages[$code]);
		}

		$baseCode = floor($code / 100) * 100;

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

		$response->to(PathHelpers::addLocation($uri), $code, $phrase);

		return $response;
	}
}
