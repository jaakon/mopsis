<?php namespace Mopsis\FormBuilder\Fields;

class Checkbox extends AbstractField
{
	public function setValue($value)
	{
		$this->prop('checked', is_array($value) ? in_array($this->val(), $value) : !!$value);
	}
}
