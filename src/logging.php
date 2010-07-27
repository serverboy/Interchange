<?php

/*
Serverboy Interchange
Generic logging library

Copyright 2010 Serverboy Software; Matt Basta

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

*/

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