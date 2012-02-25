<?

class Picture {
	#$FILENAME;
	#$USERID;
	#$PICTUREID;
	#$ORIGNAME;
	#$SERVERNAME;
	#$ERROR;
    function __construct() 
    { 
        $a = func_get_args(); 
        $i = func_num_args(); 
        if (method_exists($this,$f='__construct'.$i)) { 
            call_user_func_array(array($this,$f),$a); 
        } 
    }
	function __construct2($DB,$PICTUREID) {
		$settings = parse_ini_file('config.ini.php');
		$this->PICTUREID = $PICTUREID;
		$c = $DB->Pictures;
		$result = $c->findOne( array("_id" => new MongoId($PICTUREID)  ) );
		if ($result) {
			$this->FILENAME = $result['filename'];
			$this->ORIGNAME = $result['oldname'];
			$this->UPLOAD_DIR = $settings['UPLOAD_DIR'];
			$this->CACHE_DIR = $settings['CACHE_DIR'];
			$this->SERVERNAME = $result['servername'];
			$this->WIDTH = $result['width'];
			$this->HEIGHT = $result['height'];
			$this->ERROR = 0;
			$this->getLinks();
			$this->NOTFOUND = 0;
		} else 
			$this->NOTFOUND = 1;
	}
	function __construct4($ORIGNAME,$PICTUREID,$FILENAME,$ERROR) {
		$this->ORIGNAME = $ORIGNAME;
		$this->PICTUREID = $PICTUREID;
		$this->FILENAME = $FILENAME;
		$this->ERROR = $ERROR;
		$settings = parse_ini_file('config.ini.php');
		$this->UPLOAD_DIR = $settings['UPLOAD_DIR'];
		$this->CACHE_DIR = $settings['CACHE_DIR'];
		$this->SERVERNAME = $settings['SERVERNAME'];
		if ($ERROR === 0) {
			$this->getLinks();
		}
	}
	function getLinks() {
		$result = array();
		$this->FLINK = "http://".$this->SERVERNAME.'/pict/'.$this->PICTUREID;
		$this->DIRECT = "http://".$this->SERVERNAME.$this->UPLOAD_DIR.$this->FILENAME;
		$this->CACHE = "http://".$this->SERVERNAME.$this->CACHE_DIR.$this->FILENAME;
		$this->HTML = "<a target=\"_blank\" href=\"".$this->DIRECT."\"><img src=\"".$this->CACHE."\"></a>";
		$this->EHTML = "<a target=\"_blank\" href=\"".$this->DIRECT."\"><img src=\"".$this->DIRECT."\"></a>";
	}
}


?>
