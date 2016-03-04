<?php
namespace Mopsis\FormBuilder\Fields;

use Exception;
use Mopsis\FormBuilder\Contracts\Resizable;
use Mopsis\FormBuilder\FieldFactory;

class Select extends AbstractField implements Resizable
{
    public function getValue()
    {
        return $this->find('.//option[selected]')->val();
    }

    public function setValue($value)
    {
        foreach ($this->find('.//option') as $node) {
            $option = FieldFactory::create($node);
            $option->prop('selected', $option->val() === (string) $value);
        }
    }

    public function updateSize()
    {
        if (!$this->hasAttr('size') || ctype_digit($this->attr('size'))) {
            return;
        }

        $size = count($this->find('descendant-or-self::optgroup | descendant-or-self::option'));

        if ($this->attr('size') === 'auto') {
            $this->attr('size', $size);

            return;
        }

        if (preg_match('/\{(\d*),(\d*)\}/', $this->attr('size'), $m)) {
            $this->attr('size', between($size, $m[1], $m[2]));

            return;
        }

        throw new Exception('invalid value "' . $this->attr('size') . '" for attribute "size"');
    }
}
