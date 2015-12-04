<?php namespace Mopsis\Extensions\SwiftMailer;

use Swift_Image;
use Swift_Mailer;
use Swift_MailTransport;
use Swift_Message;
use Swift_Mime_Message;
use Swift_SmtpTransport;

class Mailer extends Swift_Mailer
{
	public function __construct()
	{
		$transport = Swift_MailTransport::newInstance();

		if (config('mail.host') && config('mail.port')) {
			$transport = Swift_SmtpTransport::newInstance(config('mail.host'), config('mail.port'), config('mail.encryption'));

			if (config('mail.username') && config('mail.password')) {
				$transport->setUsername(config('mail.username'))->setPassword(config('mail.password'));
			}
		}

		parent::__construct($transport);
	}

	public static function quickSend($recipient, $subject, $textBody = null, $htmlBody = null, $embedImages = false)
	{
		$mailer  = new static;
		$message = static::newMessage()->setTo($recipient)->setSubject($subject);

		if ($htmlBody !== null) {
			if ($embedImages && preg_match_all('/<img [^>]*src="((http:\/\/.+?\/)?([^"]+?))"/i', $htmlBody, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $m) {
					$htmlBody = str_replace($m[1], $message->embed(Swift_Image::fromPath('public/' . $m[3])), $htmlBody);
				}
			}

			$message->setBody($htmlBody, 'text/html');
		}

		$message->addPart($textBody ?: 'This email is only available as HTML version', 'text/plain');

		return $mailer->send($message);
	}

	public static function newMessage()
	{
		$message = Swift_Message::newInstance()->setFrom([config('mail.from') => static::encodeName(config('mail.fromName'))]);

		if (config('mail.replyto')) {
			$message->setReplyTo(config('mail.replyto'));
		}

		return $message;
	}

	public static function encodeName($name)
	{
		return '=?UTF-8?B?' . base64_encode($name) . '?=';
	}

	public function send(Swift_Mime_Message $message, &$failedRecipients = null)
	{
		if (config('mail.subject')) {
			$message->setSubject(config('mail.subject') . $message->getSubject());
		}

		return parent::send($message, $failedRecipients);
	}
}
