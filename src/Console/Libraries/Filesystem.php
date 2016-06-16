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
    }

    public function createFile($file, $template, $replacements, $override)
    {
        $file = '/' . 'app' . '/' . preg_replace('/Bare(\w+\.php)$/', '$1', $file);

        if (file_exists(APPLICATION_PATH . $file) && !$override) {
            return '<error>file already exists: ' . $file . '</error>';
        }

        $result = @file_put_contents(APPLICATION_PATH . $file, $this->fillPlaceholders(file_get_contents($template), $replacements));

        if ($result === false) {
            return '<error>file could not be written: ' . $file . '</error>';
        }

        return '<info>file created: ' . $file . '</info>';
    }

    public function findTemplateForAction($name)
    {
        return $this->findTemplate('Module/Action/', $name);
    }

    public function findTemplateForDomain($name)
    {
        return TEMPLATE_REPOSITORY . '/Module/Domain/' . $name . '.php.tpl';
    }

    public function findTemplateForResponder($name)
    {
        return $this->findTemplate('Module/Responder/', $name);
    }

    public function makeDirectory($module, $folder)
    {
        $path = '/app/' . $module . '/' . $folder;

        if (is_dir(APPLICATION_PATH . $path)) {
            return '<error>directory already exists: ' . $path . '</error>';
        }

        mkdir(APPLICATION_PATH . $path, 0777, true);

        return '<info>directory created: ' . $path . '</info>';
    }

    public function snakeCase($string)
    {
        return preg_replace_callback('/([A-Z])/', function ($match) {
            return '-' . strtolower($match[1]);
        }, lcfirst($string));
    }

    protected function fillPlaceholders($content, $replacements)
    {
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    protected function findTemplate($path, $name)
    {
        return TEMPLATE_REPOSITORY . '/' . $path . (file_exists($path . $name . '.php.tpl') ? $name : 'Generic') . '.php.tpl';
    }
}
