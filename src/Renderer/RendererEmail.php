<?php namespace Mopsis\Renderer;

class RendererEmail extends RendererHtml implements iRenderer
{

//=== PUBLIC METHODS ===========================================================

	public function toString()
	{
		$converter = new \CssToInlineStyles(mb_utf8_decode(parent::__toString()));

		$converter->setUseInlineStylesBlock(true);
		$converter->setStripOriginalStyleTags(true);
		$converter->setCleanup(true);

		return str_replace('EUR', '&#8364;', $converter->convert());
	}
}
