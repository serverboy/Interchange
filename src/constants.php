<?php

// Settings for Interchange, the magic PHP framework!

// If a URL requests a directory without including a trailing slash, should the request be redirected?
define("REDIRECT_TRAILING_SLASH", false);

// If a URL requests a directory without including a trailing slash, should the request be processed anyway?
// Note: If set to true, the app will appear to work as if the slash was present.
define("HANDLE_TRAILING_SLASH", false);

// This should be changed to a random string. Random string generator:
// http://www.random.org/strings/?num=1&len=20&digits=on&upperalpha=on&unique=on
define('SECRET', 'THISISASECRET');

// Comment these constants out to disable memcached support.
define('IXG_MEMCACHED', 'localhost'); # Hostname for the Memcached server
define('IXG_MEMCACHED_PORT', 11211); # The port for the Memcached server
define('IXG_MEMCACHED_TYPE', 'MEMCACHED'); # (MEMCACHED|MEMCACHE) - Which PECL extension should be accessed