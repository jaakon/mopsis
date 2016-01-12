<?php namespace Mopsis\FormBuilder;

use FluentDOM\Element;

class FieldFactory
{
	public static function create(Element $node)
	{
		switch ($node->tagName) {
			case 'input':
				return static::createInput($node);
			case 'select':
				return static::createSelect($node);
			case 'option':
				return new Fields\Option($node);
			case 'textarea':
				return new Fields\Textarea($node);
		}

		return new Fields\GenericElement($node);
	}

	protected static function createInput(Element $node)
	{
		switch ($node->getAttribute('type')) {
			case 'checkbox':
				return new Fields\Checkbox($node);
			case 'radio':
				return new Fields\Radio($node);
		}

		return new Fields\Input($node);
	}

	protected static function createSelect(Element $node)
	{
		if ($node->hasAttribute('multiple')) {
			return new Fields\MultiSelect($node);
		}

		return new Fields\Select($node);
	}
}
