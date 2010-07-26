<?php

$redirect_codes = array(
	301 => '301 Moved Permanently',
	302 => '302 Found',
	303 => '303 See Other',
	// 304 to be implemented through a script or by Interchange
	// 305 to be implemented through a script
	// 306 may not be used due to its deprecated status
	307 => '307 Temporary Redirect'
);

$error_codes = array(
	400 => '400 Bad Request',
	401 => '401 Unauthorized',
	// 402 may not be used due to its reserved status
	403 => '403 Forbidden',
	404 => '404 Not Found',
	405 => '405 Method Not Allowed',
	406 => '406 Not Acceptable',
	// 407 to be implemented through a script
	// 408 to be implemented by Interchange
	// 409 to be implemented by Interchange
	410 => '410 Gone',
	// 411 to be implemented through a script
	// 412 to be implemented through a script
	// 413 to be implemented by Interchange
	// 414 to be implemented by Interchange
	// 415 to be implemented through a script or by Interchange
	// 416 to be implemented by Interchange
	// 417 to be implemented by Interchange
	
	500 => '500 Internal Server Error',
	501 => '501 Not Implemented'
	// 502 may not be used by a script and will not be serviced by Interchange
	// 503 to be implemented through a script
	// 504 may not be used by a script and will not be serviced by Interchange
	// 505 to be implemented by Interchange
);