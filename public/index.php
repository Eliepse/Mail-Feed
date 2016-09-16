<?php

require '../vendor/autoload.php';

use App\MailCache;
use Eliepse\Cache\Cache;
use Eliepse\Cache\CacheFile;
use Eliepse\Config\ConfigFactory;
use PhpImap\Mailbox;

include_once "../App/escape-email.php";


$_server = ConfigFactory::getConfig('server');
$_global = ConfigFactory::getConfig('global');
$cache = new Cache();
$mail_cache = new MailCache();
$mailbox = null;

$mail_list = $cache->readOrWrite('mail-list', function (CacheFile $cachefile) use ($_server, $mail_cache, $mailbox, $_global) {

	$mailbox = new Mailbox($_server->server, $_server->username, $_server->password);

	// Est-ce que le script a déjà été executé, ou est-il inègre ?
	if ($cachefile->isFileExist()) {

		// On récupère la liste des mails en cache
		$last_mails = json_decode($cachefile->getData());

		// On récupère la liste des mails supprimés
		$deleted = $mailbox->searchMailbox('DELETED');

		// On vérifie les mails supprimés
		$deleted = array_intersect($deleted, $last_mails);

		// On dé-cache les mails supprimés
		foreach($deleted as $key => $id) {

			$mail_cache->remove($id);
			unset($last_mails[ $key ]);

		}

		// On vérifie si les mails en cache sont expirés
		foreach ($last_mails as $key => $id) {

			if ($mail_cache->isCacheEntryExpired($id, $_global->get('mail-expiration'))) {

				$mail_cache->remove($id);
				unset($last_mails[ $key ]);

			}

		}

		// On génère la recherche IMAP pour
		// récupérer les mails du jour
		$date = $cachefile->getModifiedAt();
		$str_date = $date->format('d-M-Y G\:i');
		$imap_query = "ALL SINCE \"$str_date\"";

		// On effectue la recherche
		$new_mails = array_reverse($mailbox->searchMailbox($imap_query));

		// On enlève les entrée du jour en double (déjà sauvegardée)
		$new_mails = array_diff($new_mails, $last_mails);

		// On ajoute les nouveaux emails à la liste
		$mails = array_merge($new_mails, $last_mails);

	} else {

		$date = new DateTime('@' . (time() - $_global->get('mail-expiration')));
		$str_date = $date->format('d-M-Y G\:i');
		$imap_query = "ALL SINCE \"$str_date\"";

		$mails = array_reverse($mailbox->searchMailbox($imap_query));

	}

	return json_encode($mails);

}, $_global->get('mailbox-check-delay'), Cache::$_no_delete);

$mail_list = json_decode($mail_list);

?>

<html>
<head>
	<title><?= $_global->get('title') ?></title>
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
		
		<div class="mail mail--closed" id="<?= $mail->id ?>">
			
			<div class="mail-header">
				<p class="mail-info">Le <span class="mail-info-date"><?= date_format(new DateTime($mail->date), "d-m-Y à H:i:s") ?></span> par <span
						class="mail-info-from"><?= $mail->fromName ?></span></p>
				<h2 class="mail-title"><?= removeEmail($mail->subject, $_server->username); ?></h2>
			</div>

			<div class="actions">
				<div class="action--close">Close news</div>
			</div>

			<div class="mail-content">
				<?php
				echo escapeEmail($mail, $_server);
				?>
			</div>

			<div class="actions">
				<div class="action--open">Open news</div>
				<div class="action--close">Close news</div>
			</div>

		</div>
		
		<?php
	}
	?>

	<script src="script.js"></script>

</div>

</body>
</html>

