<?php
// Copyright 1999-2014. Parallels IP Holdings GmbH.
class Modules_Sidekick_ContentInclude extends pm_Hook_ContentInclude
{

    public function getHeadContent()
    {
        $platform    = pm_ProductInfo::getPlatform();
        $is_admin    = pm_Session::getClient()->isAdmin();
        $is_reseller = pm_Session::getClient()->isReseller();
        $is_client   = pm_Session::getClient()->isClient();
        $langulage   = pm_Locale::getCode();
        $apiResponse = pm_ApiRpc::getService()->call("<server><get><gen_info/></get></server>");
        $mode        = $apiResponse->server->get->result->gen_info->mode;

        if ('standard' == $mode) {
            $view = 'service_provider';
        } else {
            $view = 'power_user';
        }

        $data = array(
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
        return "<script type=\"text/preloaded\" data-provider=\"sidekick\">$data</script>
        <script type=\"text/javascript\" src=\"//loader.sidekick.pro/platforms/1a17ab63-9f83-4e8c-9375-f0b6e4a0998a.js\"></script>
        ";
    }

}
