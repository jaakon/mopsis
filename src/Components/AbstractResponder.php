<?php
namespace Mopsis\Components;

use Aura\Accept\Accept;
use Aura\Web\Request;
use Aura\Web\Response;
use Mopsis\Contracts\Payload;

abstract class AbstractResponder
{
    protected $accept;

    protected $allowHtmlFragments = true;

    protected $available = [
        'text/html'        => 'html',
        'application/json' => 'json',
        'application/xml'  => 'xml'
    ];

    protected $payload;

    protected $request;

    protected $response;

    protected $template;

    protected $view;

    public function __construct(Accept $accept, Request $request, Response $response, View $view, Payload $payload)
    {
        $this->accept   = $accept;
        $this->request  = $request;
        $this->response = $response;
        $this->view     = $view;
        $this->payload  = $payload;

        $this->init();
    }

    public function __invoke()
    {
        $this->setStatus();
        $this->resolvePayload();

        return $this->response;
    }

    public function setPayload(Payload $payload)
    {
        $this->payload = $payload->dissolve($this->payload);

        return $this;
    }

    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    protected function init()
    {
        // to be used to prefill the payload
    }

    protected function renderView($template = null)
    {
        if (!$this->negotiateMediaType()) {
            return;
        }

        switch ($this->available[$this->response->content->getType()]) {
            case 'html':
                return $this->renderViewForHtml($template);
            case 'json':
                return $this->renderViewForJson();
            case 'xml':
                return $this->renderViewForXml();
            default:
                return $this->renderViewForText();
        }
    }

    protected function resolvePayload()
    {
        $resolver = $this->payload->getName();

        if (method_exists($this, $resolver)) {
            $this->$resolver();

            return;
        }

        throw new \Exception('Missing resolver for "' . $resolver . '"');
    }

    protected function setStatus()
    {
        $this->response->status->set($this->payload->getStatus());
    }

    private function getViewPath()
    {
        return explode('\\', get_called_class())[1] . DIRECTORY_SEPARATOR;
    }

    private function negotiateMediaType()
    {
        $available = array_keys($this->available);
        $media     = $this->accept->negotiateMedia($available);

        if ($media) {
            $this->response->content->setType($media->getValue());

            return true;
        }

        $this->response->status->set(406);
        $this->response->content->setType('text/plain');
        $this->response->content->set(implode(',', $available));

        return false;
    }

    private function renderViewForHtml($template = null)
    {
        $content = $this->view
            ->setTemplate($this->getViewPath() . ($template ?: $this->template) . '.twig')
            ->assign($this->payload->get())
            ->__invoke();

        if ($this->allowHtmlFragments && $this->request->isXhr()) {
            $content = preg_replace('/.*<body[^>]*>(.*)<\/body>.*/is', '$1', $content);
        }

        $this->response->content->set($content);
    }

    private function renderViewForJson()
    {
        $this->response->content->set(json_encode($this->payload->get()));
    }

    private function renderViewForText()
    {
        $this->response->content->set(print_r($this->payload->get(), true));
    }
}
