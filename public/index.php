<?php

require '../vendor/autoload.php';

use PhpImap\Mailbox as ImapMailbox;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;

$_server = include "../config/server.php";

// 4. argument is the directory into which attachments are to be saved:
$mailbox = new PhpImap\Mailbox($_server['server'], $_server['username'], $_server['password']);

// Read all messaged into an array:
$mailsIds = $mailbox->searchMailbox('ALL');
if(!$mailsIds) {
	die('Mailbox is empty');
}

// Get the first message and save its attachment(s) to disk:
$mail = $mailbox->getMail($mailsIds[0]);

echo "<h2>" . $mail->subject . "</h2><br/>";
echo $mail->textHtml;