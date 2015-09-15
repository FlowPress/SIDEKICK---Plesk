<?php
// Copyright 1999-2014. Parallels IP Holdings GmbH.
class Sidekick_Modules_EmbedJs_ContentInclude extends pm_Hook_ContentInclude
{

    public function getJsConfig()
    {
        return [
            'dynamicVar' => date(DATE_ATOM),   
        ];
    }

    public function getJsOnReadyContent()
    {
        return 'PleskExt.sidekick.init();';
    }

    public function getJsContent()
    {
        return '// example';
    }

    public function getHeadContent()
    {
        return 'bart123<!-- bart additional content for head tag -->';
    }

    public function getBodyContent()
    {
        return '<!-- additional content for body tag -->';
    }

}
