<?php namespace Mopsis\FormBuilder;

use Mopsis\Extensions\SimpleXML\SimpleXMLElement;
use Mopsis\Extensions\SimpleXML\XMLProcessingException;

class RulesManager
{
	protected $xml;

	public function __construct($forms)
	{
		$this->xml = new SimpleXMLElement($forms);
	}

	public function getSanitizerRules($id)
	{
		$items   = $this->getItems($id);
		$results = [];

		foreach ($items as $item) {
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

	public function getUploaderRules($id)
	{
		$items   = $this->getItems($id);
		$results = [];

		foreach ($items as $item) {
			$field = $item->attr('name');
			$rules = [];

			foreach ($item->xpath('rule[@type="upload"]') as $rule) {
				$rules[] = [
					'spec'    => $rule->attr('spec'),
					'args'    => explode('|', (string)$rule->attr('args')),
					'message' => $rule->attr('suppressMessage') === 'true' ? false : $rule->text()
				];
			}

			$results[$field] = $rules;
		}

		return $results;
	}

	public function getValidatorRules($id)
	{
		$items   = $this->getItems($id);
		$results = [];

		foreach ($items as $item) {
			$field = $item->attr('name');
			$rules = [];

			foreach ($item->xpath('rule[@type="validate"]') as $rule) {
				$rules[] = [
					'spec'    => $rule->attr('spec'),
					'args'    => explode('|', (string)$rule->attr('args')),
					'message' => $rule->attr('suppressMessage') === 'true' ? false : $rule->text(),
					'mode'    => $rule->attr('failureMode') ?: 'hard'
				];
			}

			$results[$field] = $rules;
		}

		return $results;
	}

	protected function getForm($id)
	{
		$xml = $this->xml->first('//form[@id="' . $id . '"]');

		if (!$xml) {
			throw new XMLProcessingException('form "' . $id . '" cannot be found in xmlData');
		}

		return $xml;
	}

	protected function getItems($id)
	{
		return $this->getForm($id)->xpath('//item[@name]') ?: [];
	}
}
