<?php namespace Mopsis\Core;

class Mailer extends \Swift_Mailer
{
	public static function quickSend($recipient, $subject, $textBody = null, $htmlBody = null, $embedImages = false)
	{
		$mailer		= new self;
		$message	= self::newMessage()->setTo($recipient)->setSubject($subject);

		if ($htmlBody !== null) {
			if ($embedImages && preg_match_all('/<img [^>]*src="((http:\/\/.+?\/)?([^"]+?))"/i', $htmlBody, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $m) {
					$htmlBody = str_replace($m[1], $message->embed(\Swift_Image::fromPath('public/'.$m[3])), $htmlBody);
				}
			}

			$message->setBody($htmlBody, 'text/html');
		}

		$message->addPart($textBody ?: 'This email is only available as HTML version', 'text/plain');

		return $mailer->send($message);
	}

	public static function newMessage()
	{
		$message = \Swift_Message::newInstance()->setFrom([MAIL_FROM => MAIL_FROMNAME]);

		if (defined('MAIL_REPLYTO')) {
			$message->setReplyTo(MAIL_REPLYTO);
		}

		return $message;
	}

	public static function encodeName($name)
	{
		return '=?UTF-8?B?'.base64_encode($name).'?=';
	}

	public function __construct()
	{
		$transport = \Swift_MailTransport::newInstance();

		if (defined('MAIL_SERVER') && defined('MAIL_PORT')) {
			$transport = \Swift_SmtpTransport::newInstance(MAIL_SERVER, MAIL_PORT, MAIL_ENCRYPTION);

			if (defined('MAIL_USERNAME') && defined('MAIL_PASSWORD')) {
				$transport->setUsername(MAIL_USERNAME)->setPassword(MAIL_PASSWORD);
			}
		}

		parent::__construct($transport);
	}

	public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
	{
		if (defined('MAIL_SUBJECT')) {
			$message->setSubject(MAIL_SUBJECT.$message->getSubject());
		}

		return parent::send($message, $failedRecipients);
	}
}
