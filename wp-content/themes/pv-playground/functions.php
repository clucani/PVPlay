<?php

if( class_exists('emuApp') )
{
    include_once( 'class/_main.class.php' );

    global $emuPV; $emuPV = new emuPV(__FILE__);
}
else
{
    $emuPV = (object) array( 'sThemeURL' => get_bloginfo('stylesheet_directory' ) );
}


?>