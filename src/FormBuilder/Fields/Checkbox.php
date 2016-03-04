<?php
namespace Mopsis\FormBuilder\Fields;

class Checkbox extends AbstractField
{
    public function setValue($value)
    {
        $values = array_map('strval', array_wrap($value));

        $this->prop('checked', in_array($this->val(), $values));
    }
}
