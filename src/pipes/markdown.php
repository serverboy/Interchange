<?php

/*
Serverboy Interchange
Markdown Driver

This file is to be considered public domain.

*/

function execute_markdown($data) {
	static $markdown;
	
	if(!$markdown) {
		require(IXG_PATH_PREFIX . "pipes/engines/markdown/markdown.php");
		$markdown = true;
	}
	
	return Markdown($data);
	
}