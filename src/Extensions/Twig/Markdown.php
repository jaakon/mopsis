<?php namespace Mopsis\Extensions\Twig;

use Aptoma\Twig\Extension\MarkdownExtension;

class Markdown extends MarkdownExtension
{
	public function getFilters()
	{
		return [
			new \Twig_SimpleFunction('markdown', [$this, 'parseMarkdown'], [
				'pre_escape' => 'html',
				'is_safe' => ['html']
			])
		];
	}
}
