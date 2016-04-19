<?php
namespace Mopsis\FormBuilder;

use Mopsis\Extensions\SimpleXML\SimpleXMLElement;
use Mopsis\Extensions\SimpleXML\XMLProcessingException;
use Mopsis\FormBuilder\Contracts\Resizable;
use Mopsis\Security\Csrf;
use stdClass;

/**
 * @property LayoutProvider $layout
 */
class FormBuilder
{
    const NO_GROUPS = '@@no-groups@@';

    protected $config;

    protected $layout;

    protected $strict;

    protected $xml;

    public function __construct($xmlData, bool $strict = false)
    {
        $this->xml    = (new SimpleXMLElement($xmlData))->first('/formbuilder');
        $this->strict = $strict;
    }

    public function getForm($id, $url, stdClass $config)
    {
        $xml = $this->xml->first('forms/form[@id="' . $id . '"]');

        if (!$xml) {
            throw new XMLProcessingException('form "' . $id . '" cannot be found in xmlData');
        }

        $this->config = $config;
        $this->layout = LayoutProvider::create($this->xml, $xml->attr('layout'), $this->strict);

        $data = array_merge($this->loadDefaults($xml), $config->settings['@global'] ?: [], [
            'form.url'  => $url,
            'form.csrf' => $this->addCsrfToken()
        ]);

        return $this->fillFormValues($this->buildNode($xml, $data));
    }

    protected function addCsrfToken()
    {
        $token = Csrf::generateToken();

        Csrf::addToken($token);

        return '<input type="hidden" name="csrfToken" value="' . $token->key . '/' . $token->value . '">';
    }

    protected function addValues(array $data, $prefix, array...$values)
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

    protected function buildAddedOptions(array $options, array $baseData)
    {
        $layout    = $this->layout->getHtmlForItem($baseData['item.type'], 'options');
        $optGroups = $this->prepareOptionGroups($options);
        $html      = '';
        $no        = 0;

        if (count($optGroups, \COUNT_RECURSIVE) > 1) {
            foreach ($optGroups as $group => $options) {
                if ($group !== static::NO_GROUPS) {
                    $html .= '<optgroup label="' . htmlentities($group) . '">';
                }

                foreach ($options as $value => $text) {
                    $data = $this->addValues($baseData, 'option', [
                        'no'    => ++$no,
                        'value' => $value
                    ]);
                    $data['option.id']   = $data['item.id'] . '-' . $no;
                    $data['option.text'] = htmlentities($text ?: $value);
                    $html .= $this->fillPlaceholder($layout, $data);
                }

                if ($group !== static::NO_GROUPS) {
                    $html .= '</optgroup>';
                }
            }
        }

        return $html;
    }

    protected function buildItem(SimpleXMLElement $xml, array $data)
    {
        $data['item.id'] = $data['form.id'] . '-' . $data['item.name'];

        if (is_array($this->config->settings[$data['item.name']])) {
            $data = array_merge($data, $this->config->settings[$data['item.name']]);
        }

        if ($xml->has('rule[@spec="required"]')) {
            $data['item.required'] = 'required';
        }

        if ($xml->has('option')) {
            $data['item.options'] = $this->buildOptions($xml->xpath('option'), $data);
        }

        $addedOptions = $this->config->options[$data['item.name']];

        if (is_array($addedOptions) && count($addedOptions)) {
            $data['item.options'] .= $this->buildAddedOptions($addedOptions, $data);
        }

        if ($xml->has('help')) {
            $data['item.help'] = $this->buildNode($xml->first('help'), $data);
        }

        return $this->fillPlaceholder($this->layout->getHtmlForItem($data['item.type']), $data);
    }

    protected function buildNode(SimpleXMLElement $xml, array $baseData)
    {
        $tagName = $xml->getName();
        $data    = $this->addValues($baseData, $tagName, $xml->attributes(), ['text' => $xml->text()]);

        if ($tagName === 'item') {
            return $this->buildItem($xml, $data);
        }

        foreach ($xml->all('*[not(self::defaults)]') as $i => $node) {
            $data[$tagName . '.content'] .= $this->buildNode($node, $this->addValues($data, $tagName, ['no' => $i]));
        }

        return $this->fillPlaceholder($this->layout->getHtml($tagName), $data);
    }

    protected function buildOptions(array $options, array $baseData)
    {
        $html   = '';
        $layout = $this->layout->getHtmlForItem($baseData['item.type'], 'options');

        foreach ($options as $i => $xml) {
            /**
             * @var SimpleXMLElement $xml
             */
            $data = $this->addValues($baseData, 'option', $xml->attributes(), [
                'no'   => $i,
                'id'   => $baseData['item.id'] . '-' . $i,
                'text' => htmlentities($xml->text() ?: $xml->attr('value'))
            ]);

            $html .= $this->fillPlaceholder($layout, $data);
        }

        return $html;
    }

    protected function fillFormValues($html)
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

            if (!empty($m[2]) && is_array($value)) {
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

    protected function loadDefaults(SimpleXMLElement $xml)
    {
        $defaults = [];

        foreach ($xml->xpath('defaults/default') ?: [] as $default) {
            /**
             * @var SimpleXMLElement $default
             */
            $defaults[$default->attr('name')] = $default->attr('value');
        }

        return $defaults;
    }

    protected function prepareOptionGroups(array $options)
    {
        if (!isset($options['data'])) {
            return [static::NO_GROUPS => $options];
        }

        $results = [];

        foreach ($options['data'] as $entry) {
            $group = $options['group'] ? $entry[$options['group']] : static::NO_GROUPS;
            $key   = $entry[$options['key']];
            $value = $entry[$options['value']];

            if (!is_array($results[$group])) {
                $results[$group] = [];
            }

            $results[$group][$key] = $value;
        }

        return $results;
    }
}
