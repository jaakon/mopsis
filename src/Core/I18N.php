<?php namespace Mopsis\Core;

use Symfony\Component\Translation\TranslatorInterface as Translator;

class I18N
{
	protected $preferredLanguage;
	protected $fallbackLanguage;
	protected $appliedLanguage;

	protected $isInitialized = false;
	protected $filePath;

	public function __construct($preferredLanguage, $fallbackLanguage)
	{
		$this->preferredLanguage = strtolower($preferredLanguage);
		$this->fallbackLanguage  = strtolower($fallbackLanguage);
		$this->appliedLanguage   = $this->preferredLanguage;
	}

	public function get($key, array $replace = [], $locale = null)
	{
		return $this->translator->get($key, $replace, $locale);
	}

	public function getAppliedLanguage()
	{
		return $this->appliedLanguage;
	}

	public function getAvailableLanguages()
	{
		$languages = array_map(
			function ($dir) {
				return strtolower(str_replace($this->filePath, '', $dir));
			},
			glob($this->filePath.'*', GLOB_ONLYDIR)
		);

		return $languages;
	}

	public function has($key, $locale = null)
	{
		return $this->translator->has($key, $locale);
	}

	public function choice($key, $number, array $replace = [], $locale = null)
	{
		return $this->translator->choice($key, $number, $replace, $locale);
	}

	public function init()
	{
		$this->failAfterInit();

		$availableLanguages = $this->getAvailableLanguages();

		if (!count($availableLanguages)) {
			throw new \RuntimeException('No language files were found.');
		}

		if (!in_array($this->fallbackLanguage, $availableLanguages)) {
			throw new \RuntimeException('Invalid fallback language "'.$this->fallbackLanguage.'"');
		}

		if (count($availableLanguages) === 1) {
			$this->appliedLanguage = array_pop($availableLanguages);
		} elseif (!in_array($this->preferredLanguage, $availableLanguages)) {
			$this->appliedLanguage = $this->getHttpAcceptLanguage($availableLanguages, $this->fallbackLanguage);
		}

		\App::set('translator.locale', $this->appliedLanguage);
		\App::set('translator.path', $this->filePath);

		$this->translator = \App::make('Translator');
		$this->translator->setFallback($this->fallbackLanguage);

		$this->isInitialized = true;

		return $this;
	}

	public function isInitialized()
	{
		return $this->isInitialized;
	}

	public function setFilePath($filePath)
	{
		$this->failAfterInit();
		$this->filePath = $filePath;

		return $this;
	}

	protected function failAfterInit()
	{
		if ($this->isInitialized()) {
			throw new \BadMethodCallException('This ' . __CLASS__ . ' object is already initalized.');
		}
	}

	protected function getHttpAcceptLanguage(array $allowedLanguages, $defaultLanguage)
	{
		if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			return $defaultLanguage;
		}

		$currentLang = $defaultLanguage;
		$currentQ    = 0;

		foreach (preg_split('/,\s*/', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $language) {
			if (!preg_match('/^([a-z]{1,8}(?:-[a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $language, $m)) {
				continue;
			}

			$langCodes   = explode('-', $m[1]);
			$langQuality = isset($m[2]) ? (float)$m[2] : 1.0;

			while (count($langCodes)) {
				if (in_array(strtolower(implode('-', $langCodes)), $allowedLanguages)) {
					if ($langQuality > $currentQ) {
						$currentLang = strtolower(implode('-', $langCodes));
						$currentQ    = $langQuality;
						break;
					}
				}

				array_pop($langCodes);
			}
		}

		return $currentLang;
	}
}
