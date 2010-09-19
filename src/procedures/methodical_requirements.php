<?php

/*
Serverboy Interchange
Methodical endpoint requirements

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

abstract class methods_base {
	public static $session;
	public static $path;
	public static $keyval;
	public function __construct() {
		global $session, $path, $keyval;
		self::$session =& $session;
		self::$path =& $path;
		self::$keyval =& $keyval;
	}
}

// This should implement: http://code.djangoproject.com/wiki/HttpResponse
class HttpResponse {
	public $data = '';
	public function __construct($response) {
		$this->data = $response;
	}
	public function output() {echo $this->data;}
}

