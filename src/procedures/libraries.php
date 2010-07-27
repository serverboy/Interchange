<?php

/*
Serverboy Interchange
Library handler

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

foreach($libraries as $lib)
	require("./libraries/$lib.php");
function getLib($lib) {
	static $libs;
	
	if (isset($libs[$lib])) return $libs[$lib];
	
	require("./libraries/$lib.php");
	
	$lib_ref = "lib_$lib";
	$nlib = new $lib_ref();
	$libs[$lib] =& $nlib;
	
	if(method_exists($nlib, 'init')) $nlib->init();
	
	return $nlib;
}

unset($libraries);
