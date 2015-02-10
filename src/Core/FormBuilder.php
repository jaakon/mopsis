<?php namespace Mopsis\Core;

class FormBuilder
{
	const NO_GROUPS   = '@@no-groups@@';

	private $_xml     = null;
	private $_options = [];

	public function __construct($configFile = null)
	{
		libxml_use_internal_errors(true);

		$dom = new \DOMDocument;

		if (!($this->_xml = simplexml_load_file($configFile ?: CORE_FORMS, 'SimpleXMLElement', LIBXML_NOCDATA))) {
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
		if (!($xml = $this->_xml->xpath('//form[@id="'.$id.'"]')[0])) {
			throw new \Exception('form "'.$id.'" not found');
		}

		if (is_array($data) && count($data['options'])) {
			$this->_options = $data['options'];
		}

		$form = $this->_buildForm($xml, $url, $this->_loadLayout($xml->attributes()->layout));
		return is_array($data) ? $this->_setFormData($form, $data['values'], $data['errors']) : $form;
	}

	public function getRules($formId)
	{
		$rules = [];

		foreach ($this->_xml->xpath('//form[@id="'.$formId.'"]//item[@name]') as $item) {
			$name = (string) $item->attributes()->name;

			if (!isset($rules[(string) $item->attributes()->name])) {
				$rules[$name] = [];
			}

			foreach ($item->xpath('rule[@type]') as $rule) {
				$args = $rule->attributes()->args ? ':'.$rule->attributes()->args : '';
				$rules[$name][$rule->attributes()->type.$args] = (string) $rule;
			}
		}

		return $rules;
	}

	private function _addCsrfToken()
	{
		$token = Security::generateToken();
		return '<input type="hidden" class="autosave-exclude" name="'.$token->key.'" value="'.$token->value.'">';
	}

	private function _addValues($data, $prefix, $attributes1, $attributes2 = [])
	{
		foreach ($attributes1 as $key => $value) {
			$data[$prefix.'.'.$key] = (string) $value;
		}

		foreach ($attributes2 as $key => $value) {
			$data[$prefix.'.'.$key] = (string) $value;
		}

		return $data;
	}

	private function _buildBlocks($form, $inheritedData, $layout)
	{
		$html = '';

		foreach ($form->xpath('block') as $i => $block) {
			$data  = $this->_addValues($inheritedData, 'block', $block->attributes(), ['no' => $i]);
			$html .= $this->_fillPlaceholder($layout['block']['before'].$this->_buildRows($block, $data, $layout).$layout['block']['after'], $data);
		}

		return $html;
	}

	private function _buildForm($form, $url, $layout)
	{
		$data = $this->_addValues($this->_loadDefaults($form), 'form', $form->attributes(), ['url' => $url]);
		$html = $layout['form']['before'].$this->_addCsrfToken().$this->_buildBlocks($form, $data, $layout).$layout['form']['after'];

		return preg_replace('/\{\w+\.\w+\}/', '', $this->_fillPlaceholder($html, $data));
	}

	private function _buildItems($row, $inheritedData, $layout)
	{
		$html = '';

		foreach ($row->xpath('item') as $i => $item) {
			$data = $this->_addValues($inheritedData, 'item', $item->attributes(), [
				'no'       => $i,
				'required' => count($item->xpath('rule[@type="required"]')) ? 'required' : null,
				'text'     => (string) $item
			]);

			$data['item.id']      = $data['form.id'].'-'.$data['item.name'];
			$data['item.options'] = $this->_buildOptions($item, $data, $layout);

			if ($data['item.requires'] && !$data['item.'.$data['item.requires']]) {
				continue;
			}

			if (count($help = $item->xpath('help'))) {
				$data['item.help'] = $this->_fillPlaceholder($layout['help']['before'].$help[0].$layout['help']['after'], $data);
			}

			$itemLayout = $this->_getItemLayout($layout, $data['item.type']);
			$html      .= $this->_fillPlaceholder($itemLayout['before'].$itemLayout['element'].$itemLayout['after'], $data);
		}

		return $html;
	}

	private function _buildOptions($item, $inheritedData, $layout)
	{
		$html    = '';
		$layout  = $this->_getItemLayout($layout, $inheritedData['item.type']);
		$options = $item->xpath('option');

		if (count($options)) {
			foreach ($options as $i => $option) {
				$data                = $this->_addValues($inheritedData, 'option', $option->attributes(), ['no' => $i]);
				$data['option.id']   = $data['item.id'].'-'.$i;
				$data['option.text'] = htmlentities($option ?: $data['option.value']);
				$html               .= $this->_fillPlaceholder($layout['options'], $data);
			}
		}

		$optGroups = $this->_prepareOptions($this->_options[$inheritedData['item.name']]);

		if (count($optGroups, \COUNT_RECURSIVE) > 1) {
			foreach ($optGroups as $group => $options) {
				if ($group !== static::NO_GROUPS) {
					$html .= '<optgroup label="'.htmlentities($group).'">';
				}

				foreach ($options as $value => $text) {
					$data                = $this->_addValues($inheritedData, 'option', ['no' => ++$i, 'value' => $value]);
					$data['option.id']   = $data['item.id'].'-'.$i;
					$data['option.text'] = htmlentities($text ?: $value);
					$html               .= $this->_fillPlaceholder($layout['options'], $data);
				}

				if ($group !== static::NO_GROUPS) {
					$html .= '</optgroup>';
				}
			}
		}

		return $html;
	}

	private function _prepareOptions($options)
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

	private function _buildRows($block, $inheritedData, $layout)
	{
		$html = '';

		foreach ($block->xpath('row') as $i => $row) {
			$data  = $this->_addValues($inheritedData, 'row', $row->attributes(), ['no' => $i]);

			if ($items = $this->_buildItems($row, $data, $layout)) {
				$html .= $this->_fillPlaceholder($layout['row']['before'].$items.$layout['row']['after'], $data);
			}
		}

		return $html;
	}

	private function _fillPlaceholder($html, $data)
	{
		foreach ($data as $key => $value) {
			$html = str_replace('{'.$key.'}', $value, $html);
		}

		return $html;
	}

	private function _getItemLayout($layout, $type)
	{
		return array_merge($layout['items']['default'], $layout['items'][$type] ?: []);
	}

	private function _getStringFromXml($xml, $path, $default = null)
	{
		return ($node = $xml->xpath($path)) ? (string) $node[0] : $default;
	}

	private function _loadDefaults($form)
	{
		if (!count($attributes = $form->xpath('defaults/attribute'))) {
			return [];
		}

		$defaults = [];

		foreach ($attributes as $attribute) {
			$defaults[(string) $attribute->attributes()->name] = (string) $attribute->attributes()->value;
		}

		return $defaults;
	}

	private function _loadLayout($layoutId, $anchestors = [])
	{
		$xml       = $this->_xml->xpath('//layout[@id="'.$layoutId.'"]')[0];
		$extends   = $xml->attributes()->extends;
		$layout    = [
			'form'  => ['before' => null, 'after' => null],
			'block' => ['before' => null, 'after' => null],
			'row'   => ['before' => null, 'after' => null],
			'help'  => ['before' => null, 'after' => null],
			'items' => []
		];

		if ($extends) {
			if (in_array($extends, $anchestors)) {
				throw new \Exception('loop detected while extending "'.$layoutId.'"');
			}

			$anchestors[] = $extends;
			$layout       = $this->_loadLayout($extends, $anchestors);
		}

		foreach (['form', 'block', 'row', 'help'] as $element) {
			$layout[$element] = [
				'before' => $this->_getStringFromXml($xml, $element.'/before', $layout[$element]['before']),
				'after'  => $this->_getStringFromXml($xml, $element.'/after', $layout[$element]['after'])
			];
		}

		$items = $layout['items'];

		if ($itemsXml = $xml->xpath('items')[0]) {
			foreach ($itemsXml as $item) {
				if (!isset($items[$item->getName()])) {
					$items[$item->getName()] = [];
				}

				foreach (['before', 'element', 'options', 'after'] as $subtype) {
					if (($string = $this->_getStringFromXml($item, $subtype, false)) !== false) {
						$items[$item->getName()][$subtype] = $string;
					}
				}
			}
		}

		$layout['items'] = $items;

		return $layout;
	}

	private function _setFormData($html, $values, $errors)
	{
		$dom = \pQuery::parseStr($html);

		foreach ($dom->query('input,select,textarea') as $node) {
			$key   = preg_match('/(.+)\[(.*)\]$/', $node->attr('name'), $m) ? $m[1] : $node->attr('name');
			$value = empty($m[2]) ? $values[$key] : $values[$key][$m[2]];

			if (in_array($key, $errors)) {
				$node->addClass('validation-error');
			}

			$this->_updateSelectOptions($node);

			if ($value === null) {
				continue;
			}

			switch ($node->tagName()) {
				case 'input':
					$this->_fillInputNode($node, $value);
					break;
				case 'select':
					$this->_fillSelectNode($node, $value);
					break;
				case 'textarea':
					if (is_array($value)) {
						$value = implode(PHP_EOL, $value);
					}
					$node->val($node->attr('data-encoding') === 'base64' ? base64_encode($value) : escape_html($value));
					break;
			}
		}

		return $dom->html();
	}

	private function _fillInputNode($node, $value)
	{
		switch ($node->attr('type')) {
			case 'checkbox':
				$node->prop('checked', is_array($value) ? in_array($node->attr('value'), $value) : !!$value);
				break;
			case 'radio':
				$node->prop('checked', $node->attr('value') === $value);
				break;
			default:
				$node->val(str_replace('&quot;', '&amp;quot;', escape_html($value)));
				break;
		}
	}

	private function _fillSelectNode($node, $value)
	{
		if (!is_array($value)) {
			$node->val($value);
			return;
		}

		foreach ($node->query('option') as $option) {
			$option->prop('selected', in_array($option->attr('value'), $value));
		}
	}

	private function _updateSelectOptions($node)
	{
		if ($node->tagName() === 'select' && $node->attr('size') === 'auto') {
			$node->attr('size', count($node->query('option,optgroup')));
		}
	}
}
