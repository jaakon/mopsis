<?php
namespace Mopsis\Renderer;

class RendererTwig implements iRenderer
{
    private $_template   = null;
    private $_useCache   = false;
    private $_data       = [];
    private $_forms      = [];
    private $_extensions = [];
    private $_functions  = [];
    private $_filters    = [];

//=== PUBLIC METHODS ===========================================================

    public function addExtension($extension)
    {
        $this->_extensions[] = $extension;
        return $this;
    }

    public function addFunction($name, $function = null)
    {
        $this->_functions[$name] = $function ?  : $name;
        return $this;
    }

    public function addFilter($name, $filter = null)
    {
        $this->_filters[$name] = $filter ?  : $name;
        return $this;
    }

    public function assign($data)
    {
        $this->_data = array_merge($this->_data, object2array($data));
        return $this;
    }

    public function display()
    {
        if (!headers_sent()) {
            header('X-Frame-Options: SAMEORIGIN');
            header('Content-Type: text/html; charset=UTF-8');
        }

        die($this->toString());
    }

    public function prefillForm($formId, \Mopsis\Validation\ValidationFacade $facade)
    {
        $this->_initializeForm($formId);

        if ($facade->isValid()) {
            return $this;
        }

        $this
        ->setFormValues($formId, $facade->getRawRequest()->toArray())
        ->setFormErrors($formId, $facade->getInvalidFields())
        ->assign(['errors' => $facade->getErrors()]);

        return $this;
    }

    public function setFormErrors($formId, array $data)
    {
        $this->_initializeForm($formId);
        $this->_forms[$formId]['errors'] = $data;

        return $this;
    }

    public function setFormOptionsX($formId, $select, $data, $key = null, $value = null, $optgroup = null)
    {
        $this->_initializeForm($formId);

        if ($data instanceof \Mopsis\Core\Collection) {
            $data = $data->toArray();
        }

        if (!is_array($data) || !count($data)) {
            return $this;
        }

        if ($key === null || $value === null) {
            $this->_forms[$formId]['options'][$select] = array_map(function ($element) {
                return htmlspecialchars($element, ENT_COMPAT, 'UTF-8', false);
            }, $data);
            return $this;
        }

        $options = [];

        foreach ($data as $entry) {
            if (is_array($entry)) {
                $entry = (object) $entry;
            }

            $options[$entry->{ $key} . ($optgroup === null ? '' : '|' . $entry->{ $optgroup})] = htmlspecialchars($entry->{ $value}, ENT_COMPAT, 'UTF-8', false);
        }

        $this->_forms[$formId]['options'][$select] = $options;

        return $this;
    }

    public function setFormOptions($formId, array $data)
    {
        $this->_initializeForm($formId);

        foreach ($data as $select => $options) {
            $this->_forms[$formId]['options'][$select] = $options;
        }

        return $this;
    }

    public function setFormValues($formId, array $data1)
    {
        $this->_initializeForm($formId);

        foreach (array_slice(func_get_args(), 1) as $data) {
            foreach ($data as $key => $value) {
                $this->_forms[$formId]['values'] = array_merge($this->_forms[$formId]['values'], implode_objects($value, $key, '.'));
            }
        }

        return $this;
    }

    public function setTemplate($template)
    {
        $this->_template = $template;
        return $this;
    }

    public function toString()
    {
        $loader = new \Twig_Loader_Filesystem('application/views/');
        $twig   = new \Twig_Environment($loader, $this->_useCache ? [
                'cache'       => CORE_CACHE,
                'auto_reload' => false,
                'autoescape'  => true
            ] : [
                'cache'       => false,
                'auto_reload' => true,
                'autoescape'  => true
            ]);

        $twig->addExtension(new \Mopsis\Twig\Formbuilder(['forms' => $this->_forms]));

        foreach ($this->_extensions as $extension) {
            $twig->addExtension($extension);
        }

        foreach ($this->_filters as $name => $filter) {
            $twig->addFilter(new \Twig_SimpleFilter($name, $filter));
        }

        foreach ($this->_functions as $name => $function) {
            $twig->addFunction(new \Twig_SimpleFunction($name, $function));
        }

        $html = $twig->render($this->_template . '.twig', $this->_data);

        while (preg_match('/<(.+?)>\s*<attribute name="(.+?)" value="(.+?)">/', $html, $m)) {
            $html = str_replace($m[0], '<' . $m[1] . ' ' . $m[2] . '="' . $m[3] . '">', $html);
        }

        return $html;
    }

    public function useCache($boolean)
    {
        $this->_useCache = $boolean;
        return $this;
    }

    private function _initializeForm($formId)
    {
        if (empty($formId)) {
            throw new \Exception('form id must not be empty');
        }

        if (!isset($this->_forms[$formId])) {
            $this->_forms[$formId] = ['values' => [], 'options' => [], 'errors' => []];
        }
    }
}
