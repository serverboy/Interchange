<?php

/*
Serverboy Interchange
HTTP status codes

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