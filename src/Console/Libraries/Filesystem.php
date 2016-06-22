<?php
namespace Mopsis\Console\Libraries;

class Filesystem
{
    public function __construct()
    {
        if (!defined('APPLICATION_PATH')) {
            define('APPLICATION_PATH', str_replace('\\', '/', realpath(__DIR__ . '/../../../../../..')));
        }

        if (!defined('TEMPLATE_REPOSITORY')) {
            define('TEMPLATE_REPOSITORY', str_replace('\\', '/', realpath(__DIR__ . '/../Templates')));
        }

        if (!defined('VIEWS_PATH')) {
            define('VIEWS_PATH', realpath(APPLICATION_PATH . '/resources/views'));
        }
    }

    public function createClass($file, $content, $override)
    {
        return $this->createFile(
            '/app/' . preg_replace('/Bare(\w+)$/', '$1', $file) . '.php',
            APPLICATION_PATH,
            $content,
            $override
        );
    }

    public function createMigration($file, $content, $override)
    {
        return $this->createFile(
            '/config/migrations/' . time() . '_' . $file . '.php',
            APPLICATION_PATH,
            $content,
            $override
        );
    }

    public function createView($file, $content, $override)
    {
        return $this->createFile(
            '/' . preg_replace('/Bare(\w+)$/', '$1', $file) . '.tpl',
            VIEWS_PATH,
            $content,
            $override
        );
    }

    public function findTemplateForAction($name)
    {
        return $this->findTemplate('Module/Action/', $name);
    }

    public function findTemplateForDomain($name)
    {
        return $this->findTemplate('Module/Domain/', $name);
    }

    public function findTemplateForMigration($name)
    {
        return $this->findTemplate('Migration/', $name);
    }

    public function findTemplateForResponder($name)
    {
        return $this->findTemplate('Module/Responder/', $name);
    }

    public function findTemplateForView($name)
    {
        return $this->findTemplate('View/', $name);
    }

    public function makeDirectory($module, $folder, $override)
    {
        $path = '/app/' . $module . '/' . $folder;

        if (is_dir(APPLICATION_PATH . $path)) {
            return $override ? null : '<error>directory already exists: ' . $path . '</error>';
        }

        mkdir(APPLICATION_PATH . $path, 0777, true);

        return '<info>directory created: ' . $path . '</info>';
    }

    protected function createFile($file, $path, $content, $override)
    {
        if (file_exists($path . $file) && !$override) {
            return '<error>file already exists: ' . $file . '</error>';
        }

        $result = @file_put_contents($path . $file, $content);

        if ($result === false) {
            return '<error>file could not be written: ' . $file . '</error>';
        }

        return '<info>file created: ' . $file . '</info>';
    }

    protected function findTemplate($path, $name)
    {
        $file        = TEMPLATE_REPOSITORY . '/' . $path . $name;
        $genericFile = TEMPLATE_REPOSITORY . '/' . $path . 'Generic';

        if (file_exists($file)) {
            return $file;
        }

        if (file_exists($genericFile)) {
            return $genericFile;
        }

        throw new \Exception('neither "' . $file . '" nor "' . $genericFile . '" could be found!');
    }
}
