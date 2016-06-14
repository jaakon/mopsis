<?php
namespace Mopsis\Extensions\Twig\Markdown;

class Parsedown extends \Parsedown
{
    protected $attributes = [];

    private static $instances = [];

    public static function instance($name = 'default')
    {
        if (isset(static::$instances[$name])) {
            return static::$instances[$name];
        }

        $instance = new static();

        static::$instances[$name] = $instance;

        return $instance;
    }

    public function setAttributes($blockType, array $attributes)
    {
        $this->attributes[$blockType] = $attributes;
    }

    protected function element(array $element)
    {
        if (count($this->attributes[$element['name']])) {
            if (!isset($element['attributes'])) {
                $element['attributes'] = [];
            }

            $element['attributes'] = array_merge($element['attributes'], $this->attributes[$element['name']]);
        }

        return parent::element($element);
    }
}
