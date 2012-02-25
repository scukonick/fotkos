<?
require_once 'UserWorker.php';
require_once 'DBWorker.php';
require_once 'User.php';
$dbworker = new DBWorker();
$DB = $dbworker->getConnection();
$userworker = new UserWorker($DB);
$user = $userworker->doApiAuth($_COOKIE);

if (! $user) {
	print ("Not authorized");
	exit();
} else {

if ($_SERVER['REQUEST_METHOD'] == 'GET' && preg_match('/^\/api\/getPictures\?from=(\d+)&limit=(\d+)/',$_SERVER['REQUEST_URI'], $matches)) {
	$Pictures = $user->findPicturesByNum($_GET['from'],$_GET['limit']);
	require_once('templates/ApiPicturesJs.php');
#	var_dump($Pictures);	
}

}
