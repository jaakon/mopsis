<?php
namespace Mopsis\Console\Libraries;

class StringHelper
{
    public function fillTemplate($template, $data)
    {
        return $this->findAndReplace(file_get_contents($template), $data);
    }

    public function singularize($string)
    {
        return str_singular($string);
    }

    public function snakeCase($string)
    {
        return preg_replace_callback('/([A-Z])/', function ($match) {
            return '-' . strtolower($match[1]);
        }, lcfirst($string));
    }

    protected function findAndReplace($string, $replacements)
    {
        return str_replace(
            array_map(function ($key) {
                return '{{' . strtoupper($key) . '}}';
            }, array_keys($replacements)),
            array_values($replacements),
            $string
        );
    }
}
