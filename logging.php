<?php

class logging {
	
	public static function exception($message, $severity = 'ERROR') {
		self::writeLog($severity . "\t" . time());
		self::writeLog($message);
		return false;
	}
	public static function writeLog($data, $file = null, $indent = 0) {
		if(!isset($file))
			$file = fopen(IXG_LOG, 'a');
		if(is_string($data)) {
			fwrite($file, "\n");
			fwrite($file, str_repeat(' ', $indent));
			fwrite($file, $data);
		} elseif(is_array($data))
			foreach($data as $line)
				self::writeLog($line, $file, $indent + 1);
		if($indent == 0)
			fclose($file);
	}
	
}