<?php namespace Mopsis\FormBuilder;

class FormBuilder
{
	const NO_GROUPS = '@@no-groups@@';

	protected $xml;
	protected $config;
	protected $options = [];

	public function __construct($configFile = null)
	{
		libxml_use_internal_errors(true);

		if (!($this->xml = simplexml_load_file($configFile ?: CORE_FORMS, 'SimpleXMLElement', LIBXML_NOCDATA))) {
			foreach (libxml_get_errors() as $error) {
				echo '<pre class="debug">';
				print_r($error);
				echo '</pre>';
			}
			libxml_clear_errors();
			die();
		}
	}

	public function getForm($id, $url, $data = null)
	{
		if (!($xml = $this->xml->xpath('//form[@id="' . $id . '"]')[0])) {
			throw new \Exception('form "' . $id . '" not found');
		}

		if (is_array($data) && count($data['options'])) {
			$this->options = $data['options'];
		}

		$form = $this->buildForm($xml, $url, $this->loadLayout($xml->attributes()->layout));

		return is_array($data) ? $this->setFormData($form, $data['values'], $data['errors']) : $form;
	}

	public function getSanitizerRules($formId)
	{
		$results = [];

		foreach ($this->xml->xpath('//form[@id="' . $formId . '"]//item[@name]') as $item) {
			$field = (string)$item->attributes()->name;
			$rules = [];

			foreach ($item->xpath('rule[@type="sanitize"]') as $rule) {
				$rules[] = [
					'spec'  => (string)$rule->attributes()->spec,
					'args'  => (string)$rule->attributes()->args,
					'blank' => isset($rule->attributes()->blankValue) ? (string)$rule->attributes()->blankValue : null
				];
			}

			if (count($rules)) {
				$results[$field] = $rules;
			}
		}

		return $results;
	}

	public function getUploaderRules($formId)
	{
		$results = [];

		foreach ($this->xml->xpath('//form[@id="' . $formId . '"]//item[@type="files" and @name]') as $item) {
			$field = (string)$item->attributes()->name;
			$rules = [];

			foreach ($item->xpath('rule[@type="upload"]') as $rule) {
				$rules[] = [
					'spec'    => (string)$rule->attributes()->spec,
					'args'    => (string)$rule->attributes()->args,
					'message' => (string)$rule
				];
			}

			$results[$field] = $rules;
		}

		return $results;
	}

	public function getValidatorRules($formId)
	{
		$results = [];

		foreach ($this->xml->xpath('//form[@id="' . $formId . '"]//item[@name]') as $item) {
			$field = (string)$item->attributes()->name;
			$rules = [];

			foreach ($item->xpath('rule[@type="validate"]') as $rule) {
				$rules[] = [
					'spec'    => (string)$rule->attributes()->spec,
					'args'    => (string)$rule->attributes()->args,
					'message' => (string)$rule,
					'mode'    => (string)$rule->attributes()->failureMode ?: 'hard'
				];
			}

			$results[$field] = $rules;
		}

		return $results;
	}

	protected function addCsrfToken()
	{
		$token = Security::generateToken();

		return '<input type="hidden" name="' . $token->key . '" value="' . $token->value . '">';
	}

	protected function addValues($data, $prefix, $attributes1, $attributes2 = [])
	{
		foreach ($attributes1 as $key => $value) {
			$data[$prefix . '.' . $key] = (string)$value;
		}

		foreach ($attributes2 as $key => $value) {
			$data[$prefix . '.' . $key] = (string)$value;
		}

		return $data;
	}

	protected function buildBlocks($form, $inheritedData, $layout)
	{
		$html = '';

		foreach ($form->xpath('block') as $i => $block) {
			$data = $this->addValues($inheritedData, 'block', $block->attributes(), ['no' => $i]);
			$html .= $this->fillPlaceholder($layout['block']['before'] . $this->buildRows($block, $data, $layout) . $layout['block']['after'], $data);
		}

		return $html;
	}

	protected function buildForm($form, $url, $layout)
	{
		$data = $this->addValues($this->loadDefaults($form), 'form', $form->attributes(), ['url' => $url]);
		$html = $layout['form']['before'] . $this->addCsrfToken() . $this->buildBlocks($form, $data, $layout) . $layout['form']['after'];

		return preg_replace('/\{\w+\.\w+\}/', '', $this->fillPlaceholder($html, $data));
	}

	protected function buildItems($row, $inheritedData, $layout)
	{
		$html = '';

		foreach ($row->xpath('item') as $i => $item) {
			$data = $this->addValues($inheritedData, 'item', $item->attributes(), [
				'no'       => $i,
				'required' => count($item->xpath('rule[@spec="required"]')) ? 'required' : null,
				'text'     => (string)$item
			]);

			$data['item.id'] = $data['form.id'] . '-' . $data['item.name'];
			$data['item.options'] = $this->buildOptions($item, $data, $layout);

			if ($data['item.requires'] && !$data['item.' . $data['item.requires']]) {
				continue;
			}

			if (count($help = $item->xpath('help'))) {
				$data['item.help'] = $this->fillPlaceholder($layout['help']['before'] . $help[0] . $layout['help']['after'], $data);
			}

			$itemLayout = $this->getItemLayout($layout, $data['item.type']);
			$html .= $this->fillPlaceholder($itemLayout['before'] . $itemLayout['element'] . $itemLayout['after'], $data);
		}

		return $html;
	}

	protected function buildOptions($item, $inheritedData, $layout)
	{
		$html    = '';
		$layout  = $this->getItemLayout($layout, $inheritedData['item.type']);
		$options = $item->xpath('option');

		if (count($options)) {
			foreach ($options as $i => $option) {
				$data = $this->addValues($inheritedData, 'option', $option->attributes(), ['no' => $i]);
				$data['option.id'] = $data['item.id'] . '-' . $i;
				$data['option.text'] = htmlentities($option ?: $data['option.value']);
				$html .= $this->fillPlaceholder($layout['options'], $data);
			}
		}

		$optGroups = $this->prepareOptions($this->options[$inheritedData['item.name']]);

		if (count($optGroups, \COUNT_RECURSIVE) > 1) {
			foreach ($optGroups as $group => $options) {
				if ($group !== static::NO_GROUPS) {
					$html .= '<optgroup label="' . htmlentities($group) . '">';
				}

				foreach ($options as $value => $text) {
					$data = $this->addValues($inheritedData, 'option', ['no' => ++$i, 'value' => $value]);
					$data['option.id'] = $data['item.id'] . '-' . $i;
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

	protected function buildRows($block, $inheritedData, $layout)
	{
		$html = '';

		foreach ($block->xpath('row') as $i => $row) {
			$data = $this->addValues($inheritedData, 'row', $row->attributes(), ['no' => $i]);

			if ($items = $this->buildItems($row, $data, $layout)) {
				$html .= $this->fillPlaceholder($layout['row']['before'] . $items . $layout['row']['after'], $data);
			}
		}

		return $html;
	}

	protected function fillPlaceholder($html, $data)
	{
		foreach ($data as $key => $value) {
			$html = str_replace('{' . $key . '}', $value, $html);
		}

		return $html;
	}

	protected function getItemLayout($layout, $type)
	{
		return array_merge($layout['items']['default'], $layout['items'][$type] ?: []);
	}

	protected function getStringFromXml($xml, $path, $default = null)
	{
		return ($node = $xml->xpath($path)) ? (string)$node[0] : $default;
	}

	protected function loadDefaults($form)
	{
		if (!count($attributes = $form->xpath('defaults/attribute'))) {
			return [];
		}

		$defaults = [];

		foreach ($attributes as $attribute) {
			$defaults[(string)$attribute->attributes()->name] = (string)$attribute->attributes()->value;
		}

		return $defaults;
	}

	protected function loadLayout($layoutId, $anchestors = [])
	{
		$xml     = $this->xml->xpath('//layout[@id="' . $layoutId . '"]')[0];
		$extends = $xml->attributes()->extends;
		$layout  = [
			'form'  => ['before' => null, 'after' => null],
			'block' => ['before' => null, 'after' => null],
			'row'   => ['before' => null, 'after' => null],
			'help'  => ['before' => null, 'after' => null],
			'items' => []
		];

		if ($xml->attributes()->config) {
			$config       = json_decode((string) $xml->attributes()->config, true);
			$this->config = (object) array_merge($config, (array) $this->config);
		}

		if ($extends) {
			if (in_array($extends, $anchestors)) {
				throw new \Exception('loop detected while extending "' . $layoutId . '"');
			}

			$anchestors[] = $extends;
			$layout       = $this->loadLayout($extends, $anchestors);
		}

		foreach (['form', 'block', 'row', 'help'] as $element) {
			$layout[$element] = [
				'before' => $this->getStringFromXml($xml, $element . '/before', $layout[$element]['before']),
				'after'  => $this->getStringFromXml($xml, $element . '/after', $layout[$element]['after'])
			];
		}

		$items = $layout['items'];

		if ($itemsXml = $xml->xpath('items')[0]) {
			foreach ($itemsXml as $item) {
				if (!isset($items[$item->getName()])) {
					$items[$item->getName()] = [];
				}

				foreach (['before', 'element', 'options', 'after'] as $subtype) {
					if (($string = $this->getStringFromXml($item, $subtype, false)) !== false) {
						$items[$item->getName()][$subtype] = $string;
					}
				}
			}
		}

		$layout['items'] = $items;

		return $layout;
	}

	protected function prepareOptions($options)
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

	protected function setFormData($html, $values, $errors)
	{
		$dom = \FluentDOM::QueryCss(utf8_decode($html), 'text/html');

		foreach ($dom->find('input,select,textarea') as $node) {
			$field = FieldFactory::create($node);
			$key   = preg_match('/(.+)\[(.*)\]$/', $field->attr('name'), $m) ? $m[1] : $field->attr('name');
			$value = preg_match('/(.+?)\.(.+)/', $key, $n) && isset($values[$n[1]]) ? $values[$n[1]][$n[2]] : $values[$key];

			if (!empty($m[2])) {
				$value = $value[$m[2]];
			}

			if (in_array($key, $errors)) {
				$field->addClass($this->config->errorClass ?: 'validation-error');
			}

			if ($field instanceof Fields\Select) {
				$field->updateSize();
			}
/*
			if ($value === null) {
				continue;
			}
*/
			$field->val($value);
		}

		return $dom;
	}
}
