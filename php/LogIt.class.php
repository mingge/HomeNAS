<?php
date_default_timezone_set('UTC');
class LogIt{
	static public function T( $log ){
		echo date("Y-m-d H:i:s") . "\t$log\n";
	}
}
?>