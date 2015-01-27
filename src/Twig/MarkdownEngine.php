<?php namespace Mopsis\Twig;

class MarkdownEngine implements \Aptoma\Twig\Extension\MarkdownEngineInterface
{
	public function transform($content)
	{
		if (!strlen(trim($content))) {
			return $content;
		}

		// Quick link for tests
		$content = preg_replace('/test-(\d+)/', '[Test #$1](/search?query=$1)', $content);

		// Revert double encoding of quotes
		$content = str_replace(['&quot;', '&#039;'], ['"', '\''], $content);

		// Setup ParseDown
		Parsedown::instance('bootstrap')->setAttributes('Table', ['class' => 'table table-bordered table-condensed']);

		// Convert markdown to html
		$content = Parsedown::instance('bootstrap')->text($content);

		// Revert encoding of code blocks
		$content = preg_replace_callback('/(<code>)(.+?)(<\/code>)/s', function ($m) {
			return $m[1].html_entity_decode($m[2]).$m[3];
		}, $content);

		// Revert encoding of ampersands in links
		$content = preg_replace_callback('/(href=")([^"]+&amp;[^"]+)(")/', function ($m) {
			return $m[1].html_entity_decode($m[2]).$m[3];
		}, $content);

		return $content;
	}

	public function getName()
	{
		return 'erusev\parsedown';
	}
}
