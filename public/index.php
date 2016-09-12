<?php

require '../vendor/autoload.php';

$_server = include "../config/server.php";
include_once "../App/escape-email.php";

$hide_email = 'noemail@nodomain.bop';
$hide_url = "no-url-here";

// 4. argument is the directory into which attachments are to be saved:
$mailbox = new PhpImap\Mailbox($_server['server'], $_server['username'], $_server['password']);

// Read all messaged into an array:
$mailsIds = $mailbox->searchMailbox('OLD');
if (!$mailsIds) {
	$mailsIds = [];
}
$mailsIds = array_reverse($mailsIds);

?>

<html>
<head>
	<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<div class="container">

	<?php

	foreach ($mailsIds as $mailID) {

		$mail = $mailbox->getMail($mailID);
		?>
		
		<div class="mail" id="<?= $mail->id ?>">
			
			<div class="mail-header">
				<p class="mail-info">Le <span class="mail-info-date"><?= date_format(new DateTime($mail->date), "d-m-Y à H:i:s") ?></span> par <span class="mail-info-from"></span><?= $mail->fromName ?></p>
				<h2 class="mail-title"><?= removeEmail($mail->subject, $_server); ?></h2>
			</div>
			
			<div class="mail-content">
				<?php
				echo escapeEmail($mail, $_server);
				?>
			</div>
		
		</div>
		
		<?php
	}
	?>

</div>

</body>
</html>

