<?php
use Eliepse\Config\Config;
use PhpImap\IncomingMail;
use Sunra\PhpSimple\HtmlDomParser;

/**
 * @param IncomingMail $mail
 * @param Config $_server
 * @return mixed
 */
function escapeEmail(IncomingMail $mail, Config $_server)
{

	$id = $mail->id;
	$content = $mail->textHtml;

	$html = HtmlDomParser::str_get_html($content);

	$body = $html->getElementByTagName('body');

	if($body) {

		$n_content = $body->innerText();

	} else {

		$n_content = $content;

	}

	// on masque l'adress email
	$n_content = removeEmail($n_content, $_server->username);

	// lazy loader
	$n_content = str_replace(' src=', ' lazyl=', $n_content);

	// No style embeded
	$n_content = preg_replace('/<style[^>]*>[^>]+<\/style>/i', '', $n_content);

	return $n_content;

}

function removeEmail($content, $email)
{
	return str_replace($email, '**email-hidden**', $content);
}