<?php

function makeDirectory($module, $folder)
{
    $path = APPLICATION_PATH . '/app/' . $module . DIRECTORY_SEPARATOR . $folder;

    if (is_dir($path)) {
        return '<error>directory already exists: ' . realpath($path) . '</error>';
    }

    mkdir($path, 0777, true);

    return '<info>directory created: ' . $path . '</info>';
}

function createFile($file, $template, $replacements, $override)
{
    $file = APPLICATION_PATH . '/app/' . preg_replace('/Bare(\w+\.php)$/', '$1', $file);

    if (file_exists($file) && !$override) {
        return '<error>file already exists: ' . realpath($file) . '</error>';
    }

    file_put_contents($file, fillPlaceholders(file_get_contents($template), $replacements));

    return '<info>file created: ' . $file . '</info>';
}

function fillPlaceholders($content, $replacements)
{
    return str_replace(array_keys($replacements), array_values($replacements), $content);
}

function findTemplateForAction($name)
{
    return findTemplate('Templates/Module/Action/', $name);
}

function findTemplateForDomain($name)
{
    return 'Templates/Module/Domain/' . $name . '.php';
}

function findTemplateForResponder($name)
{
    return findTemplate('Templates/Module/Responder/', $name);
}

function findTemplate($path, $name)
{
    return $path . (file_exists($path . $name . '.php') ? $name : 'Generic') . '.php';
}

function snakeCase($string)
{
    return preg_replace_callback('/([A-Z])/', function ($match) {
        return '-' . strtolower($match[1]);
    }, lcfirst($string));
}
