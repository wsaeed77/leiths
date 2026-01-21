<?php

class Wpjb_Upgrade_590 extends Wpjb_Upgrade_Abstract
{
    public function getVersion()
    {
        return "5.9.0";
    }

    public function execute()
    {
        $this->sql();
    }
 
}
