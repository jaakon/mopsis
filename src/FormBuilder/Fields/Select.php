<?php namespace Mopsis\FormBuilder\Fields;

use Mopsis\FormBuilder\FieldFactory;

class Select extends AbstractField
{
	public function getValue()
	{
		return $this->find('option[selected]')->val(); // not working
	}

	public function setValue($value)
	{
		foreach ($this->find('option') as $node) {
			$option = FieldFactory::create($node);
			$option->prop('selected', $option->val() === (string) $value);
		}
	}

	public function updateSize()
	{
		if ($this->attr('size') === 'auto') {
			$this->attr('size', count($this->find('descendant-or-self::optgroup | descendant-or-self::option')));
		}
	}
}
