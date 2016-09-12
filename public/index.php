<?php

require '../vendor/autoload.php';

use PhpImap\Mailbox as ImapMailbox;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;

$_server = include "../config/server.php";

// 4. argument is the directory into which attachments are to be saved:
$mailbox = new PhpImap\Mailbox($_server['server'], $_server['username'], $_server['password']);

// Read all messaged into an array:
$mailsIds = $mailbox->searchMailbox('OLD');
if (!$mailsIds) {
	$mailsIds = [];
}

?>

<html>
<head>
	<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<div class="container">
	
	<?php
	
	foreach ($mailsIds as $mailID) {
		
		$mail = $mailbox->getMail($mailsIds[0]);
		?>
		
		<div class="mail">
			
			<div class="mail-header">
				<p class="mail-info">Le <span class="mail-info-date"><?= date_format(new DateTime($mail->date), "d-m-Y à H:i:s") ?></span> par <span class="mail-info-from"></span><?= $mail->fromName ?></p>
				<h2 class="mail-title"><?= $mail->subject ?></h2>
			</div>
			
			<div class="mail-content">
				<?= $mail->textHtml ?>
			</div>
		
		</div>
		
		<?php
	}
	?>

</div>

</body>
</html>

