<?php

/**
 * 
 * Serverboy Interchange
 * Session Manager
 * 
 */

$ixg_viewstack = array();
$ixg_viewvalues = array();
class view_manager {
	
	public static function setvalue($name, $value) {
		global $ixg_viewvalues;
		$ixg_viewvalues[$name] = $value;
	}
	public static function getvalue($name) {
		global $ixg_viewvalues;
		if(isset($ixg_viewvalues[$name]))
			return $ixg_viewvalues[$name];
		return '';
	}
	public static function addview($name) {
		global $ixg_viewstack;
		$ixg_viewstack[] = $name;
	}
	public static function render() {
		global $ixg_viewstack, $path, $session;
		$file = array_shift($ixg_viewstack);
		if(empty($file)) return '';
		ob_start();
		require('./views/' . $file . '.php');
		return ob_get_clean();
	}
	
}