<?php namespace Mopsis\Extensions\Twig;

class Markdown extends \Aptoma\Twig\Extension\MarkdownExtension
{
	public function getFilters()
	{
		return [
			'markdown' => new \Twig_Filter_Method(
				$this,
				'parseMarkdown',
				[
					'pre_escape' => 'html',
					'is_safe'    => ['html']
				]
			)
		];
	}
}
