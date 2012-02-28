<?
require_once ('Picture.php');
class FileWorker {
	private $MAX_FILE_SIZE;
	private $MAX_FILE_COUNT;
	private $ALLOWED_FILES;
	private $ALLOWED_TYPES;
	private $UPLOAD_DIR;
	private $LOG_LEVEL;
	private $LOG;

	private $FILES;
	private $DB;
	private $USERID;

	private $Logger;
	private $IP;

    function randString($length){
        $result = '';
        for ($i = 0; $i < $length; $i++){   
            $num = rand(97, 122);
            $result .= chr($num);
        }
        return $result;
    }
	function __construct($FILES,$DB,$USERID) {
		$this->FILES = $FILES;
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
		
		$pid = posix_getpid();
#		require_once('Log.php');
		$conf = array('mode' => 0600, 'timeFormat' => '%F %T', 'lineFormat' => '%{timestamp} - '.$pid.' -[%{priority}] %{class}.%{function}: %{message}');
#		$this->logger = &Log::singleton('file', $this->LOG, '', $conf, $this->LOG_LEVEL);
##		$this->logger->log("Starting script for client ".$this->IP, PEAR_LOG_DEBUG);
	}

	function reArrayFiles(&$file_post) {
		$file_ary = array();
		$file_count = count($file_post['name']);
##		$this->logger->log("Rearraying $file_count files", PEAR_LOG_INFO);
		$file_keys = array_keys($file_post);

		for ($i=0; $i<$file_count; $i++) {
			foreach ($file_keys as $key) {
				$file_ary[$i][$key] = $file_post[$key][$i];
			}
		}
		return $file_ary;
	}

	function checkFiles($InputFiles){
		$OutputFiles = array();
		$count = 0;
		foreach ($InputFiles as $File){
			$count++;
##			$this->logger->log("Checking: ".$File['name'], PEAR_LOG_DEBUG);
			if ($File['name'] == '' ){
##				$this->logger->log("No files was uploaded, breaking", PEAR_LOG_DEBUG);
				break;
			} elseif ($File['size'] > $this->MAX_FILE_SIZE){
##				$this->logger->log($File['name']." is too big (".$File['size']." > ".$this->MAX_FILE_SIZE.")", PEAR_LOG_DEBUG);
				$File['Error'] = "Слишком большой файл.";
			} elseif (! in_array($File['type'],$this->ALLOWED_TYPES)){
##				$this->logger->log($File['name']." is not supported because of type: ".$File['type'], PEAR_LOG_DEBUG);
				$File['Error'] = "Неподдерживаемый типа. (Или слишком большой файл.)";
			} elseif ($File['error'] != 0 ){
##				$this->logger->log($File['name']." uploaded with error", PEAR_LOG_DEBUG);
				$File['Error'] = "Неизвестная ошибка.";
			} elseif (!getimagesize($File['tmp_name'])){
##				$this->logger->log($File['name']." can't get size of it", PEAR_LOG_DEBUG);
				$File['Error'] = "Неподдеживаемый тип.";
			} elseif ($count > $this->MAX_FILE_COUNT){
##				$this->logger->log($File['name']." too many files, breaking", PEAR_LOG_DEBUG);
				break;
			} else {
##				$this->logger->log($File['name']." all is ok", PEAR_LOG_DEBUG);
				$sizes = getimagesize($File['tmp_name']);
				if (! in_array($sizes['mime'], $this->ALLOWED_TYPES)){
					$File['Error'] = "Неподдеживаемый тип.";
				}
				$File['mime'] = $sizes['mime'];
				$File['Error'] = 0;
			}
			$OutputFiles[] = $File;
		}
		return $OutputFiles;
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
	function workFiles($InputFiles){
		$OutputPicts = array();
		foreach ($InputFiles as $File){
			if ($File['Error'] === 0 ){
				// extension
				switch($File['mime']) {
					case 'image/png':
						$extension = 'png';
						break;
					case 'image/jpeg':
						$extension = 'jpeg';
						break;
				}
				// md5
				$md5 = md5_file($File['tmp_name']);
				// directory and date
				$day = date("d");
				$month = date("m");
				$year = date("y");
				$this->checkDir($day,$month,$year);
				// new names
				$newfilename = "$year/$month/$day/".$this->USERID.$md5.'.'.$extension;
				$fileuri = $this->UPLOAD_DIR.$newfilename;
				$cacheuri = $this->CACHE_DIR.$newfilename;
				$File['destination'] = $_SERVER['DOCUMENT_ROOT'].$fileuri;
				move_uploaded_file($File['tmp_name'],$File['destination']);
				$sizes = getimagesize($File['destination']);
				$c = $this->DB->Pictures;
				$userid = new MongoID($this->USERID);
				$pictureup = array("Uploaded" => new MongoDate(),"filename" => $newfilename, "servername" => $this->SERVERNAME, "oldname" => $File['name'], "userid" => $this->USERID,'width' => $sizes[0], 'height' => $sizes[1]);
				$c->insert($pictureup);
##				$this->logger->log($File['name']." saved as ".$newfilename, PEAR_LOG_INFO);
				$Pict = new Picture($File['name'], $pictureup['_id'], $newfilename,0);
				$OutputPicts[] = $Pict;

			} else {
				$Pict = new Picture($File['name'],0,0,$File['Error']);
				$OutputPicts[] = $Pict;
			}
		}
		return $OutputPicts;
	}

	function showResult($InputFiles){
##		$this->logger->log("Showing result", PEAR_LOG_DEBUG);
		foreach ($InputFiles as $File){
			if ($File["Error"] === 0) { 
			?>
			<div type="result">
				<img src="<?= $File['cachelink']?>" alt="<?= $File['name']?>" /><a href="<?= $File['link'] ?>">Ссылка на полный размер</a>
			</div>
			<?
			} else { ?>
			<div type="result">
				<?= $File['name'] ?> - Ошибка: <?= $File['Error'] ?>
			</div>
			<?
			}
		}
	}
	function exitWithError ($errordesc) {
		print "<h3>$errordesc</h3>";
##		$this->logger->log("Exiting with error $errordesc", PEAR_LOG_INFO);
		exit(0); 
	}
	function doAllWork(){
#		$this->logger->log("Doing all work", PEAR_LOG_DEBUG);
		$this->FILES = $this->reArrayFiles($this->FILES);
		$this->FILES = $this->checkFiles($this->FILES);
		$this->FILES = $this->workFiles($this->FILES);
		return ($this->FILES);
	}

}


?>
