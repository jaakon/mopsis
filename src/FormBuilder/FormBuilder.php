<?php namespace Mopsis\FormBuilder;

use Mopsis\FormBuilder\Contracts\Resizable;
use Mopsis\Extensions\SimpleXML\SimpleXMLElement;
use Mopsis\Extensions\SimpleXML\XMLProcessingException;
use Mopsis\Security\Csrf;
use stdClass;

class FormBuilder
{
	const NO_GROUPS = '@@no-groups@@';

	protected $xml;
	protected $strict;
	protected $config;

	public function __construct($forms, $strict = false)
	{
		$this->xml    = new SimpleXMLElement($forms);
		$this->strict = $strict;
	}

	public function getForm($id, $url, stdClass $config)
	{
		$xml = $this->xml->first('//form[@id="' . $id . '"]');

		if (!$xml) {
			throw new XMLProcessingException('form "' . $id . '" cannot be found in xmlData');
		}

		$this->config = $config;
		$this->layout = $this->loadLayout($xml->attr('layout'));

		$data = array_merge($this->loadDefaults($xml), $config->settings, [
			'form.url'  => $url,
			'form.csrf' => $this->addCsrfToken()
		]);

		return $this->fillForm($this->buildNode($xml, $data));
	}

	protected function addCsrfToken()
	{
		$token = Csrf::generateToken();

		return '<input type="hidden" name="' . $token->key . '" value="' . $token->value . '">';
	}

	protected function addValues(array $data, $prefix, array ...$values)
	{
		$values = array_merge(...$values);

		if (!count($values)) {
			return $data;
		}

		foreach ($values as $key => $value) {
			$data[$prefix . '.' . $key] = is_object($value) ? (string) $value : $value;
		}

		return $data;
	}

	protected function buildItem(SimpleXMLElement $xml, array $data)
	{
		$data['item.id'] = $data['form.id'] . '-' . $data['item.name'];

		if ($xml->has('rule[@spec="required"]')) {
			$data['item.required'] = 'required';
		}

		if ($xml->has('option')) {
			$data['item.options'] = $this->buildOptions($xml->xpath('option'), $data);
		}

		if ($xml->has('help')) {
			$data['item.help'] = $this->buildNode($xml->first('help'), $data);
		}

		$layout = $this->getLayoutForItem($data['item.type']);
		$html   = $layout['before'] . $layout['element'] . $layout['after'];

		return $this->fillPlaceholder($html, $data);
	}

	protected function buildNode(SimpleXMLElement $xml, array $baseData)
	{
		$tagName = $xml->getName();
		$data    = $this->addValues($baseData, $tagName, $xml->attributes(), ['content' => $xml->text()]);

		if ($tagName === 'item') {
			return $this->buildItem($xml, $data);
		}

		if ($data[$tagName . '.content'] === null) {
			foreach ($xml->children() as $i => $node) {
				$data[$tagName . '.content'] .= $this->buildNode($node, $this->addValues($data, $tagName, ['no' => $i]));
			}
		}

		return $this->fillPlaceholder($this->getLayout($tagName), $data);
	}

	protected function buildOptions(array $options, array $baseData)
	{
		$html    = '';
		$layout  = $this->getLayoutForItem($baseData['item.type']);

		foreach ($options as $i => $xml) {
			$data = $this->addValues($baseData, 'option', $xml->attributes(), [
				'no'   => $i,
				'id'   => $baseData['item.id'] . '-' . $i,
				'text' => htmlentities($xml->text() ?: $xml->attr('value'))
			]);

			$html .= $this->fillPlaceholder($layout['options'], $data);
		}

		if (!is_array($this->config->options[$baseData['item.name']])) {
			return $html;
		}

		$optGroups = $this->buildOptionsGroups($this->config->options[$baseData['item.name']]);

		if (count($optGroups, \COUNT_RECURSIVE) > 1) {
			foreach ($optGroups as $group => $options) {
				if ($group !== static::NO_GROUPS) {
					$html .= '<optgroup label="' . htmlentities($group) . '">';
				}

				foreach ($options as $value => $text) {
					$data = $this->addValues($baseData, 'option', [
						'no'    => ++$i,
						'value' => $value
					]);
					$data['option.id']   = $data['item.id'] . '-' . $i;
					$data['option.text'] = htmlentities($text ?: $value);
					$html .= $this->fillPlaceholder($layout['options'], $data);
				}

				if ($group !== static::NO_GROUPS) {
					$html .= '</optgroup>';
				}
			}
		}

		return $html;
	}

	protected function buildOptionsGroups(array $options)
	{
		if (!isset($options['data'])) {
			return [static::NO_GROUPS => $options];
		}

		$results = [];

		if (!isset($options['group'])) {
			foreach ($options['data'] as $entry) {
				$results[$entry[$options['key']]] = $entry[$options['value']];
			}

			return [static::NO_GROUPS => $results];
		}

		foreach ($options['data'] as $entry) {
			if (!is_array($results[$entry[$options['group']]])) {
				$results[$entry[$options['group']]] = [];
			}

			$results[$entry[$options['group']]][$entry[$options['key']]] = $entry[$options['value']];
		}

		return $results;
	}

	protected function fillForm($html)
	{
		$values = $this->config->values;
		$errors = $this->config->errors;

		if (!count($values) && !count($errors)) {
			return $html;
		}

		$dom = \FluentDOM::QueryCss(utf8_decode($html), 'text/html');

		foreach ($dom->find('input,select,textarea')->filter('[name]') as $node) {
			$field = FieldFactory::create($node);
			$name  = preg_match('/(.+)\[(.*)\]$/', $field->attr('name'), $m) ? $m[1] : $field->attr('name');
			$value = $values[$name];

			if (preg_match('/(.+?)\.(.+)/', $name, $n) && isset($values[$n[1]])) {
				switch (gettype($values[$n[1]])) {
					case 'array':
						$value = $values[$n[1]][$n[2]];
						break;
					case 'object':
						$value = $values[$n[1]]->$n[2];
						break;
					case 'null':
						$value = null;
						break;
					default:
						$value = $values[$n[1]];
				}
			}

			if (!empty($m[2])) {
				$value = $value[$m[2]];
			}

			if (in_array($name, $errors)) {
				$field->addClass('validation-error');
			}

			$field->val($value);

			if ($field instanceof Resizable) {
				$field->updateSize();
			}
		}

		return $dom;
	}

	protected function fillPlaceholder($html, array $data)
	{
		foreach ($data as $key => $value) {
			$html = str_replace('{' . $key . '}', $value, $html);
		}

		return preg_replace('/\s*\{\w+\.\w+\}/', '', $html);
	}

	protected function getLayout($type)
	{
		$layout = $this->layout[$type];

		if ($this->strict && !$layout) {
			throw new XMLProcessingException('layout for element "' . $type . '" cannot be found in xmlData');
		}

		return $layout['before'] . ($layout['element'] ?: '{' . $type . '.content}'). $layout['after'];
	}

	protected function getLayoutForItem($type)
	{
		return array_merge($this->layout['items']['default'], $this->layout['items'][$type] ?: []);
	}

	protected function loadDefaults(SimpleXMLElement $xml)
	{
		$defaults = [];

		foreach ($xml->xpath('./defaults/default') ?: [] as $default) {
			$defaults[$default->attr('name')] = $default->attr('value');
		}

		return $defaults;
	}

	protected function loadLayout($id, array $anchestors = [])
	{
		$xml = $this->xml->first('//layout[@id="' . $id . '"]');

		if (!$xml) {
			throw new XMLProcessingException('layout "' . $id . '" cannot be found in xmlData');
		}

		$layout  = [];
		$extends = $xml->attr('extends');

		if ($extends) {
			if (in_array($extends, $anchestors)) {
				throw new XMLProcessingException('loop detected while extending "' . $id . '"');
			}

			$anchestors[] = $id;
			$layout       = $this->loadLayout($extends, $anchestors);
		}

		foreach ($xml->children() as $node) {
			$tagName = $node->getName();

			if (!isset($layout[$tagName])) {
				$layout[$tagName] = [];
			}

			if ($tagName === 'items') {
				$layout[$tagName] = array_merge($layout[$tagName], $this->loadLayoutForItems($node));
				continue;
			}

			$layout[$tagName] = array_merge($layout[$tagName], [
				'before' => $node->text('before'),
				'after'  => $node->text('after')
			]);
		}

		return $layout;
	}

	protected function loadLayoutForItems(SimpleXMLElement $xml)
	{
		$layout = [];

		foreach ($xml->children() as $node) {
			$tagName          = $node->getName();
			$layout[$tagName] = [];

			foreach ($node->children() as $part) {
				$layout[$tagName][$part->getName()] = $part->text();
			}
		}

		return $layout;
	}
}
