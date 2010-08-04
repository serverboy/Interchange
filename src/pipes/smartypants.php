<?php

/*
Serverboy Interchange
SmartyPants Driver

This file is to be considered public domain.

*/

function execute_smartypants($data) {
	static $smartypants;
	
	if(!$smartypants) {
		require(IXG_PATH_PREFIX . "pipes/engines/smartypants/smartypants.php");
		$smartypants = true;
	}
	
	return SmartyPants($data);
	
}