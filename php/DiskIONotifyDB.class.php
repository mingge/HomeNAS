<?php

require_once( dirname(__FILE__) . "/GConf.class.php" );
require_once( dirname(__FILE__) . "/LogIt.class.php" );
class DiskIONotifyDB{
	
	public function addTask( $type, $dir, $file, $isDir ){
		$DSN = GConf::getIOTaskNotifyDBPath();
		//echo $DSN . "\n";
		try{
			$pdo = new PDO( "sqlite:" . $DSN );
			$stmt = $pdo->prepare('REPLACE INTO t_io_notify_tasks (isDir, dir, file, lastUp,type, doing) VALUES ( :isDir, :dir, :file, :lastUp, :type, 0 )');
			$stmt->bindParam( ':isDir', $isDir );
			$r = $stmt->bindParam( ':dir',   $dir );
			$r = $stmt->bindParam( ':file',  $file );
			$r = $stmt->bindParam( ':lastUp', time() );
			$r = $stmt->bindParam( ':type',  $type );
			$rtn = $stmt->execute();
		}catch (PDOException $e){
			LogIt::T( __FILE__ . "(" . __LINE__ . ")->" .  $e->getMessage() );
		}
		$stmt= null;
		$pdo = null;
	}
	
	public function getTasks(){
		$DSN = GConf::getIOTaskNotifyDBPath();

		try{
			$pdo = new PDO( "sqlite:" . $DSN );
			$now = time();
			$sql = "SELECT isDir, dir, file, lastUp, type, doing FROM t_io_notify_tasks WHERE ($now - lastUp) > 5";
			$stmt = $pdo->prepare( $sql );
			$rtn = $stmt->execute();
			
			$d = $stmt->fetchAll();
			//var_dump( $d );
			
			
		}catch (PDOException $e){
			LogIt::T( __FILE__ . "(" . __LINE__ . ")->" .  $e->getMessage() );
		}
		
		$stmt= null;
		$pdo = null;
		return $d;
	}
	
	public function delTasks( $tasks ){
		$DSN = GConf::getIOTaskNotifyDBPath();
		//echo $DSN . "\n";
		foreach( $tasks as $t ){
			try{
				$pdo = new PDO( "sqlite:" . $DSN );
				$stmt = $pdo->prepare('DELETE FROM t_io_notify_tasks WHERE (isDir=:isDir AND dir=:dir AND file=:file AND type=:type)');
				$stmt->bindParam( ':isDir', $t['isDir'] );
				$r = $stmt->bindParam( ':dir',   $t['dir'] );
				$r = $stmt->bindParam( ':file',  $t['file'] );
				$r = $stmt->bindParam( ':type',  $t['type'] );
				$rtn = $stmt->execute();
			}catch (PDOException $e){
				LogIt::T( __FILE__ . "(" . __LINE__ . ")->" .  $e->getMessage() );
			}
		}
		$stmt= null;
		$pdo = null;
	}
}
/*
$obj = new DiskIONotifyDB();
$obj->addTask( "CREATE", "/home/shares22b1/", "aaaabbb.jpg", 0);
*/
?>