<?php
namespace Mopsis\Core;

use Aura\Web\Response;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Mopsis\Extensions\Aura\Web\ResponseSender;

class Bootstrap
{
    public function initializeApplication()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(FRAMEWORK_PATH . '/definitions.php');
        $builder->addDefinitions(APPLICATION_PATH . '/config/definitions.php');

        App::initialize($builder->build());

        App::get('config')->load(APPLICATION_PATH . '/config/config.php');
        App::get('Database');
        App::get('ErrorHandler');
    }

    public function initializeEnvironment()
    {
        $dotenv = new Dotenv(APPLICATION_PATH);

        $dotenv->load();
        $dotenv->required(['APP_KEY', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD']);
        $dotenv->required('APP_ENV')->allowedValues(['local', 'staging', 'production']);

        $_SERVER = array_diff_key($_SERVER, $_ENV);
    }

    public function initializeFramework()
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

        setlocale(LC_ALL, [
            'de_DE.UTF8',
            'de-DE'
        ]);

        session_start();

        define('FRAMEWORK_PATH', realpath(__DIR__ . '/..'));
        define('APPLICATION_PATH', realpath(__DIR__ . '/../../../../..'));
        define('DEBUG', strpos($_SERVER['HTTP_USER_AGENT'], '(DEBUG)') !== false);

        chdir(APPLICATION_PATH);
    }

    public function kickstart($flushMode = null)
    {
        $this->initializeFramework();
        $this->initializeEnvironment();
        $this->initializeApplication();

        if (php_sapi_name() === 'cli') {
            return true;
        }

        $this->updateCache($flushMode);
        include APPLICATION_PATH . '/app/initialize.php';

        $response = $this->executeRoute();
        (new ResponseSender($response))->__invoke();

        return true;
    }

    protected function executeRoute()
    {
        $response = App::get('Mopsis\Core\Router')->get();

        if ($response instanceof Response) {
            return $response;
        }

        if ($response === null) {
            return $this->buildResponse(502, staticPage(502));
        }

        if ($response !== false) {
            return $this->buildResponse(203, $response);
        }

        App::get('Logger')->error('file not found: ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . ' [' . $_SERVER['HTTP_USER_AGENT'] . ']');

        return $this->buildResponse(404, staticPage(404));
    }

    protected function updateCache($flushMode)
    {
        if ($flushMode === 'all' || $flushMode === 'app') {
            if (App::has('CacheTool')) {
                App::get('CacheTool')->opcache_reset();
            }
        }

        if ($flushMode === 'all' ||  $flushMode === 'data') {
            Cache::clear();
        }

        if ($flushMode === 'all' || $flushMode === 'views') {
            App::get('Mopsis\Components\View\View')->clearCache();
        }
    }

    private function buildResponse($code, $content)
    {
        $response = App::get('Aura\Web\Response');

        $response->status->setCode($code);
        $response->content->set($content);

        return $response;
    }
}
