<?php
// Copyright 1999-2014. Parallels IP Holdings GmbH.
class Modules_Sidekick_ContentInclude extends pm_Hook_ContentInclude
{

	public function getHeadContent()
	{
		$platform      = pm_ProductInfo::getPlatform();
		$is_admin      = pm_Session::getClient()->isAdmin();
		$is_reseller   = pm_Session::getClient()->isReseller();
		$is_client     = pm_Session::getClient()->isClient();
		$langulage     = pm_Locale::getCode();
		$apiResponse   = pm_ApiRpc::getService()->call("<server><get><gen_info/></get></server>");
		$mode          = $apiResponse->server->get->result->gen_info->mode;
		$activation_id = pm_Settings::get('sidekick_activation_id');

		if ('standard' == $mode) {
			$view = 'service_provider';
		} else {
			$view = 'power_user';
		}

		// Plesk
		// $activation_id = '4ebf23b5-c364-43ab-9a18-21c9ff684068';

		// Onyx
		$activation_id = '735e24da-3c31-49ea-8265-8b3fbe546d2e';

		$data = array(
			'activation_id' => $activation_id,
			'compatibilities' => array(
				'server_os'             => $platform,
				'user_type_is_admin'    => $is_admin,
				'user_type_is_reseller' => $is_reseller,
				'user_type_is_client'   => $is_client,
				'language'              => $langulage,
				'view'                  => $view
			)
		);

		$data = json_encode($data);
		// return "<script>console.log('bart211')</script>";
		// <script type=\"text/javascript\" src=\"//loader.sidekick.pro/platforms/e7d4a916-52fe-4f7e-ad60-b5aeee13f8f8.js\"></script>
		return "<script type=\"text/preloaded\" data-provider=\"sidekick\">$data</script>
		<script>
		setTimeout(function() {
			var script = document.createElement('script');
			script.src = '//loader.sidekick.pro/platforms/e7d4a916-52fe-4f7e-ad60-b5aeee13f8f8.js';
			document.getElementsByTagName('head')[0].appendChild(script);
		}, 2000);		
		</script>
		";
	}

}
