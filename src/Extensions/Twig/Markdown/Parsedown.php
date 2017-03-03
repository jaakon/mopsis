<?php
namespace Mopsis\Extensions\Twig\Markdown;

class Parsedown extends \Parsedown
{
    protected $attributes = [];

    protected $inlineMarkerList = '!"*_&[:<>`~\\{';

    private static $instances = [];

    public function __construct()
    {
        $this->BlockTypes['{'][]  = 'Placeholder';
        $this->InlineTypes['{'][] = 'Placeholder';
    }

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

    protected function blockPlaceholder($line)
    {
        if (preg_match('/^\{(.+?)\}:[ ]*(.+?)[ ]*$/', $line['text'], $matches)) {
            $this->DefinitionData['Placeholder'][$matches[1]] = $matches[2];

            return ['hidden' => true];
        }

        return;
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

    protected function inlinePlaceholder($excerpt)
    {
        if (!isset($this->DefinitionData['Placeholder'])) {
            return;
        }

        foreach ($this->DefinitionData['Placeholder'] as $key => $value) {
            $pattern = '/\{' . preg_quote($key, '/') . '\}/i';

            if (preg_match($pattern, $excerpt['text'], $matches)) {
                return [
                    'extent' => strlen($matches[0]),
                    'markup' => $value
                ];
            }
        }

        return;
    }
}
