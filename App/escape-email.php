<?php
use Sunra\PhpSimple\HtmlDomParser;

function escapeEmail(\PhpImap\IncomingMail $mail, array $_server)
{

	$id = $mail->id;
	$content = $mail->textHtml;

	$html = HtmlDomParser::str_get_html($content);

	$body = $html->getElementByTagName('body');

	if(count($body) > 0) {

		$n_content = $body->innerText();

	} else {

		$n_content = $content;

	}


	// on masque l'adress email
	$n_content = removeEmail($n_content, $_server);

	$n_content = preg_replace_callback("#src=\"([^\"]*)\"#i", function ($matches) use ($id) {

		if (preg_match("/src=\"\S+\.(jpe?g|png|gif)[;?:@=&a-zA-Z0-9]*\"/i", $matches[0]))
			return $matches[0];
		else
			return "src='#$id'";

	}, $n_content);

	$n_content = preg_replace("#href=\"\S+\"#i", "href=\"#$id\"", $n_content);

	return $n_content;

}

function removeEmail($content, array $_server)
{
	return str_replace($_server['username'], '**email-hidden**', $content);
}