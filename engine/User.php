<?
require_once('Picture.php');

class User {
	function findPictures() {
		$c = $this->DB->Pictures;
		$result = $c->find(array('userid' => $this->USERID));
		$Pictures = array();
		foreach ($result as $raw_picture){
				$Pictures[] = new Picture($raw_picture['oldname'],$raw_picture['_id'],$raw_picture['filename'],0);
		}
		return ($Pictures);
	}
	function findPicturesByNum($from,$limit){
		$c = $this->DB->Pictures;
		$result = $c->find(array('userid' => $this->USERID))->sort( array( 'Uploaded' => -1 ) )->skip($from)->limit($limit);
		$Pictures = array();
		foreach ($result as $raw_picture){
				$Pictures[] = new Picture($raw_picture['oldname'],$raw_picture['_id'],$raw_picture['filename'],0);
		}
		return ($Pictures);
		
	}
	function setLogin($login) {
		$c = $this->DB->Users;
		$result = $c->findOne(array('login' => $login));
		if ($result) {
			return (0);
		} else {
			$c->update(array('_id' => new MongoId($this->USERID)), array('$set' => array('login' => $login)));
			$this->LOGIN = $login;
			return(1);
		}
	}
	function setPassword($passwd) {
		$md5 =  md5($passwd);
		$c = $this->DB->Users;
		$c->update(array('_id' => new MongoId($this->USERID)), array('$set' => array('passwd' => $md5)));
		$this->PASSWD = $md5;
		$this->BYPASS = 1;
		return("Пароль изменён.");
	}
	function setPassLogin($login,$passw){
		$result = array();
		if (strlen($login)<=0){
			$result['errors']='Логин не может быть пустым.';
		}
		if (! isset($result['errors'])){
			if ($this->setLogin($login) == 0){
				$result['errors'].= ' Такой логин уже занят.';
			} else {
				$this->setPassword($passw);
			}
		}
		if (! isset($result['errors']))
			return(0);
		else
			return ($result['errors']);
	}
	function getLink(){
		$c = $this->DB->Users;
		$result = $c->findOne(array('_id' => new MongoId($this->USERID),'link' => array('$exists' => true ) ));

		if ($result) {
		//	print_r($result);
			return($result['link']);
		} else {
			$md5 = md5($this->USERID.rand(0,10000));
			$c = $this->DB->Users;
			$c->update(array('_id' => new MongoId($this->USERID)), array('$set' => array('link' => $md5)));
		//	print ("<br>After inser link:");
			$result = $c->findOne(array('_id' => new MongoId($this->USERID),'link' => array('$exists' => true ) ));
		//	print_r($result);
			return ($md5);
		}
		
	}
	function getHTTPLink(){
		$link = $this->getLink();
		$httplink = "http://alexw.winlink.ru/linkin/".$link;
		return ($httplink);
	}
	function dump(){
		print ("<br>==================<br>");
		$c = $this->DB->Users;
		$result = $c->findOne(array('_id' => new MongoId($this->USERID)));
		print_r($result);
		print ("<br>==================<br>");
	}
	function getLogin(){
		if (isset($this->LOGIN)){
			return $this->LOGIN;
		} else 
			return false;
	}
}
?>
