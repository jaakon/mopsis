<?php namespace Mopsis\Extensions\Twig;

use Aptoma\Twig\Extension\MarkdownExtension;
use Twig_Filter_Method;

class Markdown extends MarkdownExtension
{
	public function getFilters()
	{
		return [
			'markdown' => new Twig_Filter_Method($this, 'parseMarkdown', [
				'pre_escape' => 'html',
				'is_safe'    => ['html']
			])
		];
	}
}
