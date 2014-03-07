<?php
require_once( dirname(__FILE__) . "/LogIt.class.php" );
require_once( dirname(__FILE__) . "/GConf.class.php" );
require_once( dirname(__FILE__) . "/DiskIONotifyDB.class.php" );

//ignore_user_abort();
//set_time_limit(0);

class DiskIONotify{

	private static $instance;
	public static function getInstance(){
		if (!isset(self::$instance)) { 
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;	
	}
	
	private $vm = array( 'wait' => array(), 'finish' => array() );
	
	public function setTimerEvent(){
		$ev = Event::signal( $this, SIGTERM, array($this, 'doTimerJob'));
		$ev->addSignal(3);
	}
	
	public function doTimerJob($no, $c){
		echo "--------------------->doTimerJob\n";
	}
	
	private function doClean(){
		$bin = GConf::$BIN_KILL_ALL;
		$cmd = "$bin -9 inotifywait";
		`$cmd`;`$cmd`;`$cmd`;`$cmd`;`$cmd`;`$cmd`;
		
		if( file_exists(GConf::$IO_NOTIFY_FIFO_FILE_PATH) )
			@unlink( GConf::$IO_NOTIFY_FIFO_FILE_PATH );
	}
	
	private function doInitialize(){
		
		$b = posix_mkfifo( GConf::$IO_NOTIFY_FIFO_FILE_PATH, 0777 );
		if( !$b ){
			LogIt::T( "DiskIONotify:doInitialize failed!" );
			return false;
		}
		
		$fifo = fopen( GConf::$IO_NOTIFY_FIFO_FILE_PATH, 'r' );
		
		stream_set_timeout( $fifo, 3 );
		$objDB = new DiskIONotifyDB();

		while( !feof( $fifo ) ){
						
			$l = trim(fgets( $fifo ));
			if( !$l ){
				continue;
			}
			
			$d = explode( "$$$$$$", $l );
			if( 4 != count( $d ) ){
				continue;
			}
			
			$date 	= $d[0];
			$action = $d[1];
			$dir	= $d[2];
			$file	= $d[3];
			$path	= $dir . $file;
			
			$need_pass = false;
			foreach( GConf::$IO_NOTIFY_PASS_NAME as $k => $v ){
				//echo "Search: $k => $v\n";
				if( $v && ( false !== strpos( $dir, $k ) || false !== strpos( $file, $k ) ) ){
					$need_pass = true;
					break;
				}
			}
			
			if( true === $need_pass ){
				continue;
			}
			
			// CREATE | CREATE,ISDIR | CLOSE_WRITE,CLOSE | ATTRIB | ATTRIB,ISDIR
			// MOVED_FROM | MOVED_TO | DELETE | DELETE,ISDIR
			
			$d1 = explode( ",", $action );
			$ract = $d1[0];

			$isDir = true;
			if( 1 == count($d1) ){
				$isDir = false;
			}
			
			if( "MODIFY" === $ract ){
				
			}else if( "CREATE" === $ract ){	
				
				if( !$isDir ){
					//if( !isset( $this->vm['wait'][$path] ) ){
					//	$this->vm['wait'][$path] = time();
					//}
				}
				
			}else if( "CLOSE_WRITE" === $ract ){
				$objDB->addTask( $ract, $dir, $file, 0);
			}else if( "ATTRIB" === $ract ){
				$objDB->addTask( $ract, $dir, $file, 0);
			}else if( "MOVED_FROM" == $ract ){
				$objDB->addTask( $ract, $dir, $file, 0);
			}else if( "MOVED_TO" == $ract ){
				$objDB->addTask( $ract, $dir, $file, 0);
			}else if( "DELETE" == $ract ){
				$objDB->addTask( $ract, $dir, $file, 0);
			}else{
				LogIt::T( "$date\t$action\t$dir\t$file" );
			}		
		} 
		fclose( $fifo );
	}
	
	private function doJob(){
		
		$this->doClean();
		
		$pid = pcntl_fork();
		if( -1 == $pid ){
			LogIt::T(  "DiskIONotify:: fork failed!\n" );
		}else if( 0 == $pid ){
			// we are the child
			$this->doInitialize();
		}else{
			// we are the parent
			sleep(1);
			
			// start shell
			$conf = GConf::$DISK_IO_NOTIFY_CONFIG_FILE_PATH;
			$bin  = GConf::$BIN_IO_NOTIFY_WAIT_PATH;
			
			// modify 的数据量太大，copy文件过程中会产生很多MODIFY事件。
			$cmd = "$bin --fromfile $conf --timefmt '%y/%m/%d-%H:%M' --format '%T$$$$$$%e$$$$$$%w$$$$$$%f' -rme attrib,move,modify,close_write,create,delete --outfile " . GConf::$IO_NOTIFY_FIFO_FILE_PATH;
			`$cmd`;
		}
	}
	
	public function run(){
		while(true){
			$this->doClean();
			$this->doJob();
		}
	}
}

//==========================

//declare(ticks = 1);

function signal_handler($signal) {
	$obj = DiskIONotify::getInstance();
	$obj->doTimerJob();
	pcntl_alarm(3);
}

pcntl_signal(SIGALRM, "signal_handler", true);
pcntl_alarm(3);
echo "SET Alarm\n";

$obj = DiskIONotify::getInstance();
//$obj->setTimerEvent();
$obj->run();
?>
