<?php

class Modules_Sidekick_License
{
	public function getPleskLicense()
	{
		return $this->getLicense('plesk');
	}

	public function getWordPressLicense()
	{
		return $this->getLicense('wordpress');
	}

	private function getLicense($type)
	{
		$licenses = pm_License::getAdditionalKeysList('ext-sidekick');
		if (0 == count($licenses)) {
			return null;
		}
		$license = reset($licenses);
		$keyBody = json_decode($license['key-body'], true);
		if (!isset($keyBody[$type])) {
			return null;
		}
		return $keyBody[$type];
    }
}
