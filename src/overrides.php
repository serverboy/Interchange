<?php

if(function_exists('rename_function')) {

	$overrides = array(
		'file_get_contents'=>array(
			'old'=>array('$a','$b','$c','$d'),
			'new'=>array('$a','true','$c','$d')
		),
		'file_put_contents'=>array(
			'old'=>array('$a','$b','$c','$d'),
			'new'=>array('$a','$b','FILE_USE_INCLUDE_PATH','$d')
		),
		'fopen'=>array(
			'old'=>array('$a','$b','$c','$d'),
			'new'=>array('$a','$b','true','$d')
		),
		'chgrp'=>array(
			'old'=>array('$a','$b'),
			'new'=>array('$a','$b'),
			'code'=>'return ixg_chgrp((file_exists($a) ? $a : (PATH_PREFIX . "/" . $a)),$b);'
		)
	);

	foreach($overrides as $func=>$args) {
		rename_function($func, 'ixg_' . $func);
		$old = implode($args['old']);
		$new = implode($args['new']);
		$code = isset($args['code']) ? $args['code'] : "return ixg_$func($new);";
		override_function(
			$func,
			$old,
			$code
		);
	}
	
	
}
