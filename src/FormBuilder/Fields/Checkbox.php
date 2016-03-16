<?php
namespace Mopsis\FormBuilder\Fields;

class Checkbox extends AbstractField
{
    public function setValue($value)
    {
        $values = array_map('strval', arrayWrap($value));

        $this->prop('checked', in_array($this->val(), $values));
    }
}
