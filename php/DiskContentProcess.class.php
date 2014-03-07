<?php
require_once( dirname(__FILE__) . "/DiskIONotifyDB.class.php" );
require_once( dirname(__FILE__) . "/LogIt.class.php" );

class DiskContentProcess{

	public function doJob(){
		$obj   = new DiskIONotifyDB();
		$tasks = $obj->getTasks();
				
		foreach ( $tasks as  $task ) {
			
			if( 1 == $task['isDir'] ){
				if( 'CREATE' == $task['type'] ){
					$this->addDir( $task['dir'] );
				}else if( 'DELETE' == $task['type'] ){
					$this->delDir( $task['dir'] );
				}else if( 'MOVED_FROM' == $task['type'] ){
					$this->delDir( $task['dir'] );
				}else if( 'MOVED_TO' == $task['type'] ){
					$this->addDir( $task['dir'] );
				}else if( 'ATTRIB' == $task['type'] ){
					// TODO: XXXXXXX
					if( $task['file'] && 0 < strlen($task['file']) ){
						$this->addDir( $task['dir'] );
					}
				}else if( 'CLOSE_WRITE' == $task['type'] ){
					// TODO: NOT USED
				}
			}else{
				if( 'CREATE' == $task['type'] ){
					$this->addFile( $task['dir'], $task['file'] );
				}else if( 'DELETE' == $task['type'] ){
					$this->delFile( $task['dir'], $task['file'] );
				}else if( 'MOVED_FROM' == $task['type'] ){
					$this->delFile( $task['dir'], $task['file'] );
				}else if( 'MOVED_TO' == $task['type'] ){
					$this->addFile( $task['dir'], $task['file'] );
				}else if( 'ATTRIB' == $task['type'] ){
					// TODO: XXXXXXX
				}else if( 'CLOSE_WRITE' == $task['type'] ){
					$this->addFile( $task['dir'], $task['file'] );
				}
			}// if
		} // foreach
		
		$obj->delTasks( $tasks );
		unset( $tasks );
	}
	
	public function addFile( $dir, $file ){
		LogIt::T( "addFile( $dir, $file )" );
	}
	
	public function addDir( $dir ){
		LogIt::T( "addDir( $dir )" );
	}
	
	public function delFile( $dir, $file ){
		LogIt::T( "delFile( $dir, $file )" );
	}
	
	public function delDir( $dir ){
		LogIt::T( "delDir( $dir )" );
	}
}

$obj = new DiskContentProcess();
$obj->doJob();

?>