<?php

/*
Serverboy Interchange
Textile Driver

This file is to be considered public domain.

*/

function execute_textile($data) {
	static $textile;
	
	if(!$textile) {
		require(IXG_PATH_PREFIX . "pipes/engines/textile/textile.php");
		$textile = new Textile;
	}
	
	return $textile->TextileThis($data);
	
}