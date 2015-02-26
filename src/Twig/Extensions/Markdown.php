<?php namespace Mopsis\Twig\Extensions;

class Markdown extends \Aptoma\Twig\Extension\MarkdownExtension
{
	public function getFilters()
	{
		return array(
			'markdown' => new \Twig_Filter_Method(
				$this,
				'parseMarkdown',
				[
					'pre_escape' => 'html',
					'is_safe'    => ['html']
				]
			)
		);
	}
}
