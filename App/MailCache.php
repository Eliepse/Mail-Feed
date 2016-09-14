<?php

namespace App;

use Eliepse\Cache\Cache;

class MailCache extends Cache
{

	public function initConfigs($url = false)
	{
		parent::initConfigs();

		if ($url === false && isset($this->cache_config->paths['mail']))
			$this->cache_path = $this->path_base . $this->cache_config->paths['mail'];
	}

}