<?php namespace Mopsis\FormBuilder;

use Mopsis\Extensions\SimpleXML\SimpleXMLElement;
use Mopsis\Extensions\SimpleXML\XMLProcessingException;

/**
 * @property SimpleXMLElement   $xml
 * @property SimpleXMLElement[] $items
 */
class RulesProvider
{
	protected $xml;
	protected $formId;
	protected $items;

	public function __construct($xmlData)
	{
		$this->xml = (new SimpleXMLElement($xmlData))->first('/formbuilder/forms');

		if (!$this->xml) {
			throw new XMLProcessingException('forms cannot be found in xmlData');
		}
	}

	public function load($formId)
	{
		if ($formId === $this->formId) {
			return $this;
		}

		$xml = $this->xml->first('form[@id="' . $formId . '"]');

		if (!$xml) {
			throw new XMLProcessingException('form "' . $formId . '" cannot be found in xmlData');
		}

		$this->items  = $xml->all('//item[@name]');
		$this->formId = $formId;

		return $this;
	}

	public function forSanitizer()
	{
		$results = [];

		foreach ($this->items as $item) {
			$field = $item->attr('name');
			$rules = [];

			foreach ($item->all('rule[@type="sanitize"]') as $rule) {
				$rules[] = [
					'spec'  => $rule->attr('spec'),
					'args'  => explode('|', $rule->attr('args')),
					'blank' => $rule->attr('blankValue') ?: null
				];
			}

			if (count($rules)) {
				$results[$field] = $rules;
			}
		}

		return $results;
	}

	public function forUploader()
	{
		$results = [];

		foreach ($this->items as $item) {
			$field = $item->attr('name');
			$rules = [];

			foreach ($item->all('rule[@type="upload"]') as $rule) {
				$rules[] = [
					'spec'    => $rule->attr('spec'),
					'args'    => explode('|', $rule->attr('args')),
					'message' => $rule->attr('suppressMessage') === 'true' ? false : $rule->text()
				];
			}

			$results[$field] = $rules;
		}

		return $results;
	}

	public function forValidator()
	{
		$results = [];

		foreach ($this->items as $item) {
			$field = $item->attr('name');
			$rules = [];

			foreach ($item->all('rule[@type="validate"]') as $rule) {
				$rules[] = [
					'spec'    => $rule->attr('spec'),
					'args'    => explode('|', $rule->attr('args')),
					'message' => $rule->attr('suppressMessage') === 'true' ? false : $rule->text(),
					'mode'    => $rule->attr('failureMode') ?: 'hard'
				];
			}

			$results[$field] = $rules;
		}

		return $results;
	}
}
