<?php
// download ticket system
include("include/init.php");
include("include/auth.php");

$act = (empty($_REQUEST["a"])? false: $_REQUEST["a"]);

if(!$auth || $act == "login")
  include("include/login.php");
elseif($act == "tlist")
  include("include/listticket.php");
elseif($act == "grant")
  include("include/newgrant.php");
elseif($act == "glist")
  include("include/listgrant.php");
else
  include("include/newticket.php");
?>