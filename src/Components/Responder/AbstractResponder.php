<?php
namespace Mopsis\Components\Responder;

use Aura\Accept\Accept;
use Aura\Web\Request;
use Aura\Web\Response;
use Mopsis\Components\Domain\Payload\PayloadInterface;
use Mopsis\Components\View\View;

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

    public function __construct(Accept $accept, Request $request, Response $response, View $view, PayloadInterface $payload)
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
        $method = $this->payload ? $this->payload->getMethod() : 'notFound';

        $this->$method();

        return $this->response;
    }

    public function setPayload(PayloadInterface $payload)
    {
        $this->payload = $payload->override($this->payload);

        return $this;
    }

    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    protected function getViewPath()
    {
        return explode('\\', get_called_class())[1] . DIRECTORY_SEPARATOR;
    }

    protected function init() {}
    protected function negotiateMediaType()
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

    protected function notRecognized()
    {
        $this->response->status->set(500);
        $this->response->content->set('Unknown domain payload status: "' . get_class($this->payload) . '"');

        return $this->response;
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

    protected function renderViewForHtml($template = null)
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

    protected function renderViewForJson()
    {
        $this->response->content->set(json_encode($this->payload->get()));
    }

    protected function renderViewForText()
    {
        $this->response->content->set(print_r($this->payload->get(), true));
    }
}
