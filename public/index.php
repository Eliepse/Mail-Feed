<?php

require '../vendor/autoload.php';

use App\MailCache;
use Eliepse\Cache\Cache;
use Eliepse\Cache\CacheFile;
use Eliepse\Config\ConfigFactory;
use PhpImap\Mailbox;

include_once "../App/escape-email.php";


$_server = ConfigFactory::getConfig('server');
$cache = new Cache();
$mail_cache = new MailCache();
$mailbox = null;

$mail_list = $cache->readOrWrite('mail-list', function (CacheFile $cachefile) use ($_server, $mail_cache, $mailbox) {

	$mailbox = new Mailbox($_server->server, $_server->username, $_server->password);

	// Est-ce que le script a déjà été executé, ou est-il inègre ?
	if ($cachefile->isFileExist()) {

		// On récupère la liste des mails en cache
		$last_mails = json_decode($cachefile->getData());

		// On vérifie s'ils sont expirés
		foreach ($last_mails as $key => $id) {

			if ($mail_cache->isCacheEntryExpired($id)) {

				$mail_cache->remove($id);
				unset($last_mails[ $key ]);

			}

		}

		// On génère la recherche IMAP à effectuer
		$date = $cachefile->getModifiedAt();
		$str_date = $date->format('j F Y');
		$imap_query = "ALL SINCE \"$str_date\"";

		// On récupère les nouveaux emails
		$new_mails = array_reverse($mailbox->searchMailbox($imap_query));

		// On ajoute les nouveaux emails
		$mails = array_unshift($last_mails, $new_mails);

	} else {

		$date = new DateTime('@' . (time() - 604800));
		$str_date = $date->format('j F Y');
		$imap_query = "ALL SINCE \"$str_date\"";

		$mails = array_reverse($mailbox->searchMailbox($imap_query));

	}


	return json_encode($mails);

}, 3600 * 3, Cache::$_no_delete);

$mail_list = json_decode($mail_list);

// 4. argument is the directory into which attachments are to be saved:
//$mailbox = new Mailbox($_server->server, $_server->username, $_server->password);

// Read all messaged into an array:
//$mailsIds = $mailbox->searchMailbox('ALL');
//if (!$mailsIds) {
//	$mailsIds = [];
//}
//$mailsIds = array_reverse($mailsIds);

?>

<html>
<head>
	<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<div class="container">

	<?php

	foreach ($mail_list as $mailID) {

		$mail = $mail_cache->readOrWrite($mailID, function () use ($mailbox, $mailID, $_server) {

			if (!$mailbox instanceof Mailbox)
				$mailbox = new Mailbox($_server->server, $_server->username, $_server->password);

			$mail = $mailbox->getMail($mailID);

			return serialize($mail);

		});

		$mail = unserialize($mail);

		?>
		
		<div class="mail" id="<?= $mail->id ?>">
			
			<div class="mail-header">
				<p class="mail-info">Le <span class="mail-info-date"><?= date_format(new DateTime($mail->date), "d-m-Y à H:i:s") ?></span> par <span
						class="mail-info-from"><?= $mail->fromName ?></span></p>
				<h2 class="mail-title"><?= removeEmail($mail->subject, $_server->username); ?></h2>
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

