<?php namespace Mopsis\Extensions\Reflection;

class ReflectionMethod extends \ReflectionMethod
{
	public function getBody()
	{
		$source    = file($this->getFileName());
		$startLine = $this->getStartLine() - 1;
		$endLine   = $this->getEndLine();

		return implode('', array_slice($source, $startLine, $endLine - $startLine));
	}
}
