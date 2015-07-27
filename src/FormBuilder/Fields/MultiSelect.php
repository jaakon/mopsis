<?php namespace Mopsis\FormBuilder\Fields;

use Mopsis\FormBuilder\FieldFactory;

class MultiSelect extends Select
{
	public function getValue()
	{
		$values = [];

		foreach ($this->find('option[selected]') as $node) {
			$option = FieldFactory::create($node);

			$values[] = $option->val();
		}

		return $values;
	}

	public function setValue($value)
	{
		if (!is_array($value)) {
			$value = [$value];
		}

		foreach ($this->find('option') as $node) {
			$option = FieldFactory::create($node);

			$option->prop('selected', in_array($option->val(), $value, true));
		}
	}
}
