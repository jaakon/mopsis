<?php namespace Mopsis\FormBuilder;

use Mopsis\Extensions\SimpleXML\SimpleXMLElement;
use Mopsis\Extensions\SimpleXML\XMLProcessingException;

class RulesManager
{
	protected $xml;
	protected $formId;
	protected $items;

	public function __construct($xmlData)
	{
		$this->xml = (new SimpleXMLElement($xmlData))->first('/formbuilder/forms');
	}

	public function __invoke($formId)
	{
		if ($formId !== $this->formId) {
			$this->items  = $this->load($formId);
			$this->formId = $formId;
		}

		return $this;
	}

	public function getSanitizerRules()
	{
		$results = [];

		foreach ($this->items as $item) {
			$field = $item->attr('name');
			$rules = [];

			foreach ($item->xpath('rule[@type="sanitize"]') as $rule) {
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

	public function getUploaderRules()
	{
		$results = [];

		foreach ($this->items as $item) {
			$field = $item->attr('name');
			$rules = [];

			foreach ($item->xpath('rule[@type="upload"]') as $rule) {
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

	public function getValidatorRules()
	{
		$results = [];

		foreach ($this->items as $item) {
			$field = $item->attr('name');
			$rules = [];

			foreach ($item->xpath('rule[@type="validate"]') as $rule) {
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

	protected function load($formId)
	{
		$xml = $this->xml->first('form[@id="' . $formId . '"]');

		if (!$xml) {
			throw new XMLProcessingException('form "' . $formId . '" cannot be found in xmlData');
		}

		return $xml->xpath('//item[@name]') ?: [];
	}
}
