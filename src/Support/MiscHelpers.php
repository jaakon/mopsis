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

	public static function redirect($url, $responseCode = 302)
	{
		if (preg_match('/^(ht|f)tps?:\/\//', $url) === 0) {
			$url = ($_SERVER['REQUEST_SCHEME'] ?: 'http') . '://' . $_SERVER['HTTP_HOST'] . PathHelpers::resolve(preg_replace('/\/+$/', '', $_SERVER['REQUEST_URI']) . '/' . $url);
		}

		if (headers_sent($file, $line)) {
			echo 'ERROR: Headers already sent in ' . $file . ' on line ' . $line . "!<br/>\n";
			echo 'Cannot redirect, please click <a href="' . $url . '">[this link]</a> instead.';
			exit;
		}

		http_response_code($responseCode);
		header('Location: ' . $url);
		exit;
	}
}
