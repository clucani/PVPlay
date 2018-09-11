<?php

class emuM_Theme extends emuManager
{
    public function init()
    {
		$this->emuApp->registerView('pv-plot');
	}

    public function loadScripts()
    {
        // JScript lib
        if( !is_admin() )
        {
            $this->emuApp->loadScript( 'pv', $this->emuApp->sThemeURL."/js/app.pv.js?".date('ymdhis'), array( 'jquery' ) );
        }
    }
}	

?>