<?php

/*
Serverboy Interchange
View manager

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

class view_manager {
	
	public static $stack = array();
	public static $values = array();
	
	public static function set_value($name, $value) {
		self::$values[$name] = $value;
	}
	public static function get_value($name, $default='') {
		if(isset(self::$values[$name]))
			return self::$values[$name];
		return $default;
	}
	public static function add_view($name) {
		self::$stack[] = $name;
	}
	public static function render() {
		global $path, $session, $keyval; // For use in the views
		$file = array_shift(self::$stack);
		if(empty($file)) return '';
		if(!is_file('./views/' . $file . '.php')) {
			return "View '$file' could not be found.";
		}
		ob_start();
		require('./views/' . $file . '.php');
		return ob_get_clean();
	}
	public static function render_as_httpresponse() {
		return new HttpResponse(self::render());
	}
	public static function render_as_value($name) {
		self::$values[$name] = self::render();
	}
	public static function render_and_pipe($through) {
		$output = self::render();
		return pipe::through($data, $through);
	}
	public static function dump() {self::$stack = array();}
	
}