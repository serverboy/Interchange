<?php

/*
Serverboy Interchange
Session management

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

class raw_driver {
	private $data = array();
	
	public function __construct() {
		if(!file_exists(IXG_RAW)) {
			file_put_contents(IXG_RAW, "{}");
		}
		$this->data = json_decode(file_get_contents(IXG_RAW));
	}
	public function destroy($id) {
		unset($this->data[$id]);
		$this->flush();
	}
	public function set($name, $data) {
		$this->data[$name] = $data;
		$this->flush();
	}
	public function get($name) {
		if(!isset($this->data[$name]))
			return null;
		return $this->data[$name];
	}
	private function flush() {file_put_contents(IXG_RAW, json_encode($this->data));}
}