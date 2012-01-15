<?php
/*
	Copyright 2011 Magic Logo LLC, all rights reserved
*/
error_reporting(E_ALL & ~E_NOTICE);
global $localpath;
$progpath=dirname(__FILE__);
include_once("{$progpath}/magiclogo_lib.php");
$localpath="{$progpath}/magiclogo";
if(!is_dir($localpath)){buildDir($localpath);}
if(!is_dir($localpath)){abortMagic("Unable to build or find {$localpath}");}
if(!preg_match('/^http/i',$_REQUEST['src'])){
	$_REQUEST['src']="http://{$_SERVER['HTTP_HOST']}{$_REQUEST['src']}";
}
//get the logo to display
$localfile=getMagicLogo($_REQUEST['src'],$_REQUEST);
//display
displayFile($localfile);
exit;
?>