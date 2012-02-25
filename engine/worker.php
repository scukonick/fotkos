<?
require_once 'UserWorker.php';
require_once 'DBWorker.php';
require_once 'User.php';
$dbworker = new DBWorker();
$DB = $dbworker->getConnection();
$userworker = new UserWorker($DB);

#print ("_POST:<br>");
#var_dump($_POST);
#print ("<br>");

// Check if not new user
$user = $userworker->doAuth($_SERVER['REQUEST_URI'],$_COOKIE,$_POST);
#print("Settgin cookie $user->SESSID<br>");
setcookie('sessid',$user->SESSID,time()+60*60*24*30,'/');

?>
<!--#include file="/ssi/head.html"-->
<?
//SHOW COLLECTION
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_SERVER['REQUEST_URI'] == '/collection/'){
?>	
<? #$Pictures = $user->findPictures(); ?>
<!--#include file="/ssi/pictures.html"-->
<?	#include 'templates/Pictures.php';
}

// SHOW ONE PICTURE
elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && preg_match('/^\/pict\/(.{10,})/',$_SERVER['REQUEST_URI'], $matches)){
	$Picture = new Picture($DB,$matches[1]);
	include 'templates/Picture.php';
}
// FUCK WORK
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['files'])) {
	require_once 'FileWorker.php';

	$fileworker = new FileWorker($_FILES['files'],$DB,$user->USERID);
	$Pictures = $fileworker->doAllWork();
	include 'templates/Pictures.php';
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bylink']) && isset($_POST['link'])) {
	require_once('Downloader.php');
	$downloader = new Downloader($_POST['link'],$DB,$user->USERID);
	$Pictures = $downloader->doAllWork();
	include 'templates/Pictures.php';
}
// SHOW RULEs
elseif ($_SERVER['REQUEST_METHOD'] == 'GET' &&  $_SERVER['REQUEST_URI'] == '/rules/'){
?>
<!--#include file="/ssi/rules.html"-->
<?
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SERVER['REQUEST_URI'] == '/settings/' && isset($_POST['passw']) && isset($_POST['action']) && ( $_POST['action'] == 'setpass')) {
	$result = $user->setPassword($_POST['passw']);
	include 'templates/Settings.php';	

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SERVER['REQUEST_URI'] == '/settings/' && isset($_POST['login']) && isset($_POST['passw']) && isset($_POST['action']) && ( $_POST['action'] == 'setpasslogin')) {
	$result = $user->setPassLogin($_POST['login'],$_POST['passw']);
	include 'templates/Settings.php';	
	
} elseif ($_SERVER['REQUEST_URI'] == '/settings/'){
	include 'templates/Settings.php';	
}


// AT THE END, MAIN PAGE
else { ?>
<!--#include file="/ssi/main.html"-->
<? } ?>
<!--#include file="/ssi/foot.html"-->
