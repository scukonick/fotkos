<?
require_once ('User.php');
class UserWorker {
	private $MAX_FILE_SIZE;
	private $MAX_FILE_COUNT;
	private $LOG;
	private $LOG_LEVEL;
	private $IP;
	private $DB;
	function __construct($DB) {
		$settings = parse_ini_file('config.ini.php');
		$this->LOG_LEVEL = $settings['LOG_LEVEL'];
		$this->LOG = $settings['LOG'];
		$this->IP = $_SERVER['REMOTE_ADDR'];
		$this->DB = $DB;
		$pid = posix_getpid();
		$conf = array('mode' => 0600, 'timeFormat' => '%F %T', 'lineFormat' => '%{timestamp} - '.$pid.' -[%{priority}] %{class}.%{function}: %{message}');
	}

	function checkSession($cookie){
		if(isset($cookie['sessid'])) {
			$sessid = $cookie['sessid'];
			$UserCollection = $this->DB->Users;
			$result = $UserCollection->findOne(array("session" => array(array('sessid'=> $sessid, "IP" => $this->IP))));
			if ($result) {
				$user = new User();
				$user->USERID = $result['_id'];
				if (isset($result['login'])){
					$user->LOGIN = $result['login'];
					$user->BYPASS = 1;
				}
				$user->DB = $this->DB;
				$user->SESSID = $sessid;
				return $user;
			} else
				return 0;
		} else
			return 0;
	}
	function randString($length){
		$result = '';
		for ($i = 0; $i < $length; $i++){
			$num = rand(97, 122);
			$result .= chr($num);
		}
		return $result;
	}

	function createCleanUser(){
		$UserCollection = $this->DB->Users;
		$sessid= $this->randString(20);
		$userarray = array("session" => array(array('sessid' => $sessid,"IP" => $this->IP)));
		$UserCollection->insert($userarray);
		$user = new User();
		$user->USERID = $userarray['_id'];
		$user->DB = $this->DB;
		$user->SESSID = $sessid;
		return ($user);
	}
	function getUserFromLP($URI,$cookie,$post) {
		if (isset($post['action']) && ($post['action'] == 'loginbylogin') && isset($post['passw']) && isset($post['login'])) {
			$login = $post['login'];
			$password = $post['passw'];
			$md5 = md5($password);
			$UserCollection = $this->DB->Users;
			$sessid= $this->randString(20);
			$lparray = array('login' => $login, 'passwd' => $md5);
			$result = $UserCollection->findOne($lparray);
			if ($result){
				$user = new User();
				$user->USERID = $result['_id'];
				$user->DB = $this->DB;
				$user->SESSID = $sessid;
				$user->LOGIN = $result['login'];
				$user->BYPASS = 1;
				$userarray = array('$push' =>array( "session" => array(array('sessid' => $sessid,"IP" => $this->IP))));
				$result = $UserCollection->update(array('_id' => new MongoId($user->USERID)), $userarray);
				return ($user);
			} else {
				return 0;
			}
		} else 
			return 0;
	}
	function getUserFromLink($URI,$cookie,$post) {
		if (preg_match('/^\/linkin\/(.{10,})/',$URI, $matches)){
			$UserCollection = $this->DB->Users;
			$result = $UserCollection->findOne(array('link' => $matches[1]));
			if ($result) {
				$sessid= $this->randString(20);
				$user = new User();
				$user->USERID = $result['_id'];
				if (isset ($result['login'])){
					$user->LOGIN = $result['login'];
					$user->BYPASS = 1;
				}
				$user->DB = $this->DB;
				$user->SESSID = $sessid;
				$userarray = array('$push' =>array( "session" => array(array('sessid' => $sessid,"IP" => $this->IP))));
				$result = $UserCollection->update(array('_id' => new MongoId($user->USERID)), $userarray);
			} else {
				$user = 0;
			}
		} else 
			$user = 0;
		return $user;

	}
	function doAuth($URI,$cookie,$post){
		$user = $this->getUserFromLink($URI,$cookie,$post);
		if (! $user)
			$user = $this->getUserFromLP($URI,$cookie,$post);
		if (! $user)
			$user = $this->checkSession($cookie);
		if (! $user)
			$user = $this->createCleanUser();
		return ($user);
	}
	function doApiAuth($cookie) {
		$user = $this->checkSession($cookie);
		return ($user);
	}
}



?>
