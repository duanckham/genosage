<?php
//error_reporting(0); 
# SET OUTPUT ENCODE
header('Content-Type: text/html; charset=UTF-8');
# SET TIME ZONE
date_default_timezone_set('Asia/Shanghai');
# OPEN SESSION
session_start();
# DEBUG
$debug_info = array();
# LOAD CORE FILE
include_once('Core/Core.core.php');
include_once('Core/Init.core.php');
include_once('Core/Sql.core.php');
include_once('Core/App.core.php');
include_once('Core/Mod.core.php');
include_once('Core/Tpl.core.php');
include_once('Core/Auth.core.php');
include_once('Core/Page.core.php');
include_once('Core/Upload.core.php');
include_once('Core/Json.core.php');
include_once('Core/Cache.core.php');
include_once('Core/Debug.core.php');
include_once('Core/Router.core.php');
include_once('Core/Channel.core.php');
?>