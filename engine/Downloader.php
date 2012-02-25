<?
# Downloader
# doAllWork return Pictures
# new - Link, DB,userid
require_once ('Picture.php');
class Downloader {
	private $UPLOAD_DIR;
	private $LOG_LEVEL;
	private $LOG;

	private $FILES;
	private $DB;
	private $USERID;

	private $Logger;
	private $IP;
	private $LINK;
	function __construct($LINK,$DB,$USERID) {
		$this->LINK = $LINK;
		$this->DB = $DB;
		$this->USERID = $USERID;
		$settings = parse_ini_file('config.ini.php');
		$this->MAX_FILE_SIZE = $settings['MAX_FILE_SIZE'];
		$this->MAX_FILE_COUNT = $settings['MAX_FILE_COUNT'];
		$this->ALLOWED_FILES = $settings['ALLOWED_FILES'];
		$this->ALLOWED_TYPES = $settings['ALLOWED_TYPES'];
		$this->UPLOAD_DIR = $settings['UPLOAD_DIR'];
		$this->CACHE_DIR = $settings['CACHE_DIR'];
		$this->LOG_LEVEL = $settings['LOG_LEVEL'];
		$this->SERVERNAME = $settings['SERVERNAME'];
		$this->LOG = $settings['LOG'];
		$this->IP = $_SERVER['REMOTE_ADDR'];

	}
	function checkDir($d,$m,$y){
#		$this->logger->log("Checking directory $y/$m/$d", PEAR_LOG_DEBUG);
		$m = $y."/".$m;
		$d = $m."/".$d;
		foreach (array($y,$m,$d) as $dir){
#			$this->logger->log("Checking directory ".$_SERVER['DOCUMENT_ROOT'].$this->UPLOAD_DIR."/".$dir, PEAR_LOG_DEBUG);
			if (! is_dir($_SERVER['DOCUMENT_ROOT'].$this->UPLOAD_DIR."/".$dir))
				mkdir($_SERVER['DOCUMENT_ROOT'].$this->UPLOAD_DIR."/".$dir);
		}
	}
	function download() {
		# At first
		$File = array();
		# Downloading
		print "Downloading $this->LINK<br>";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->LINK);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		preg_match("/\.[^.]+$/",$this->LINK,$matches);
		$st = curl_exec($ch);
		curl_close($ch);
		print "Downloaded<br>";
		# Writing to tempfile
		$tmpfname = tempnam("/tmp", "FOO");
		$handle = fopen($tmpfname, "w");
		fwrite($handle, $st);
		print($tmpfname);
		@fclose($tmpfname);
		# Renaming
		$md5 = md5_file($tmpfname);
		$day = date("d");
		$month = date("m");
		$year = date("y");
		$this->checkDir($day,$month,$year);
		$newfilename = "$year/$month/$day/".$this->USERID.$md5.$matches[0];
		$fileuri = $this->UPLOAD_DIR.$newfilename;
		$cacheuri = $this->CACHE_DIR.$newfilename;
		$File['destination'] = $_SERVER['DOCUMENT_ROOT'].$fileuri;
		rename($tmpfname,$File['destination']);
		# Write to base
		$sizes = getimagesize($File['destination']);
		$c = $this->DB->Pictures;
		$userid = new MongoID($this->USERID);
		$pictureup = array("Uploaded" => new MongoDate(),"filename" => $newfilename, "servername" => $this->SERVERNAME, "link" => $this->LINK, "userid" => $this->USERID,'width' => $sizes[0], 'height' => $sizes[1]);
		$c->insert($pictureup);
		# return result
		$Pict = new Picture($this->LINK, $pictureup['_id'], $newfilename,0);
		$OutputPicts[] = $Pict;
		return $OutputPicts;
	}
	function doAllWork(){
		$OutputPicts =  $this->download();
		return $OutputPicts;

	}

}
?>
