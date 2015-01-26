<?php namespace Mopsis;

use \Assetic\Asset\AssetCollection;
use \Assetic\Asset\GlobAsset;

class AccessLevel     extends \Addendum\Annotation {}
class TokenException  extends \UnexpectedValueException {}
class AccessException extends \InvalidArgumentException {}

class Bootstrap
{
	public function kickstart()
	{
		$this->_followLinks();
		$this->_initialize();
		$this->_doRouting();
	}

	private function _followLinks()
	{
		if (is_null($_SERVER['ROUTE'])) {
			$_SERVER['ROUTE'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		}

		switch ($_SERVER['ROUTE']) {
			case '/@info':
				phpinfo();
				die();
				break;
			case '/':
			case '':
				$_SERVER['ROUTE'] = '/home';
				break;
		}
	}

	private function _initialize()
	{
		setlocale(LC_ALL, 'de_DE.UTF8');
		session_start();

		$builder = new \DI\ContainerBuilder;
		$builder->addDefinitions(__DIR__.'/config.php');
		$builder->useAutowiring(false);

		Core\Registry::load('config/config.php');

		\App::initialize($container = $builder->build());
		\App::make('database');

		$whoops = new \Whoops\Run;
		$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
		$whoops->register();

		if (isset($_GET['clearCache'])) {
			\App::make('cache')->flush();
		}

		if (isset($_GET['clearCache']) || isset($_GET['reload'])) {
			$adapter = new \CacheTool\Adapter\FastCGI('127.0.0.1:9000');
			$cache   = \CacheTool\CacheTool::factory($adapter);

			$cache->apc_clear_cache('both');
			$cache->opcache_reset();
		}

		$this->_cacheAssets();

		include 'application/initialize.php';

		if (isset($_GET['clearCache']) || isset($_GET['reload'])) {
			$renderer->clearCache();
		}

		$container->set(Renderer\iRenderer::class, $renderer);
	}

	private function _cacheAssets()
	{
		$cache = \App::make('cache');

		$item = $cache->getItem('files/css/version');
		if (!$item->get() || isset($_GET['clearCss'])) {
			$assets = new AssetCollection([
				new GlobAsset('public/css/*.css'),
				new GlobAsset('public/css/*.less', [new \Assetic\Filter\LessphpFilter]),
				new GlobAsset('public/css/*.scss', [new \Assetic\Filter\ScssphpFilter])
			], [
				new \Assetic\Filter\CssMinFilter
			]);

			file_put_contents('public/static/main.css', $assets->dump());
			$item->set(time());
		}

		$item = $cache->getItem('files/javascript/version');
		if (!$item->get() || isset($_GET['clearJs'])) {
			$assets = new AssetCollection([
				new GlobAsset('public/js/plugins/*.js'),
				new GlobAsset('public/js/scripts/*.js')
			]);

			file_put_contents('public/static/main.js', $assets->dump());
			$item->set(time());
		}
	}

	private function _doRouting()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
			$_SERVER['REQUEST_METHOD'] = 'GET';
		}

		if (!is_null($result = \App::make('Mopsis\Core\Router')->get($_SERVER['REQUEST_METHOD'], $_SERVER['ROUTE']))) {
			if ($result instanceof Renderer\iRenderer) {
				$result->display();
			}

			die($result);
		}

		\App::make('logger')->error('file not found: '.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['ROUTE'].' ['.$_SERVER['HTTP_USER_AGENT'].']');

		http_response_code(404);
		die(defined('CORE_MISSINGPAGE') ? file_get_contents(CORE_MISSINGPAGE) : '<span style="color:#E00;font-weight:bold;">ROUTE NOT FOUND!</span>');
	}
}
