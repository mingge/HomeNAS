<?php
class GConf{
	static public $IO_NOTIFY_FIFO_FILE_PATH 	= "/tmp/IONotify.fifo";
	static public $DISK_IO_NOTIFY_CONFIG_FILE_PATH = "/home/inotify.conf";
	
	//BIN PATH
	static public $BIN_IO_NOTIFY_WAIT_PATH 	= "/usr/bin/inotifywait";
	static public $BIN_KILL_ALL					= "/usr/bin/killall";
	
	static public $IO_NOTIFY_PASS_NAME			= array( 
												'.AppleDouble' => true, 
												);
												
	static public function getIOTaskNotifyDBPath(){
		return dirname(__FILE__) . "/BasicData.sqlite";
	}
}
?>