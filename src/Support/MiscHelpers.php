<?php namespace Mopsis\Support;

class MiscHelpers
{
	public static function debug(...$args)
	{
		$ladybug = new \Ladybug\Dumper();

		$ladybug->setTheme('modern');
		$ladybug->setOption('expanded', false);
		$ladybug->setOption('helpers', ['debug']);

		echo $ladybug->dump(...$args);
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
