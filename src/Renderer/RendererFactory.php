<?php namespace Mopsis\Renderer;

class RendererFactory
{
	public static function create($type, $template = null)
	{
		switch ($type) {
			case 'email':
				return (new RendererEmail())->setTemplate($template);
				break;
			case 'json':
				return (new RendererJson())->setTemplate($template);
				break;
			case 'text':
				return (new RendererText())->setTemplate($template);
				break;
			case 'twig':
				return (new RendererTwig())->setTemplate($template);
				break;
			default:
				throw new \Exception('invalid Renderer type: "'.$type.'"');
				break;
		}
	}
}
