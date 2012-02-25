<?

class DBWorker {
	private $DBNAME;
	private $DB;
	private $LOG;
	private $LOG_LEVEL;
	private $IP;

	function __construct() {
#		require_once('Log.php');
		$settings = parse_ini_file('config.ini.php');
		$this->LOG_LEVEL = $settings['LOG_LEVEL'];
		$this->LOG = $settings['LOG'];
		$this->DBNAME = $settings['DBNAME'];
		$pid = posix_getpid();
		$conf = array('mode' => 0600, 'timeFormat' => '%F %T', 'lineFormat' => '%{timestamp} - '.$pid.' -[%{priority}] %{class}.%{function}: %{message}');
#		$this->logger = &Log::singleton('file', $this->LOG, '', $conf, $this->LOG_LEVEL);
#		$this->logger->log("Starting DBWorker", PEAR_LOG_DEBUG);

	}
	function getConnection(){
#		$this->logger->log("Creating connection to base", PEAR_LOG_DEBUG);
		$mongo = new Mongo();
		$this->DB = $mongo->selectDB($this->DBNAME) or exit (1);
		return ($this->DB);
	}
}


?>
