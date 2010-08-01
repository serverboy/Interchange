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
define('SUPER_SECRET', hash('sha256', SECRET));

// This is the chunk size that will be sent to the client. It should attempt to somewhat closely
// match the packet size for the server.
define('PACKET_SIZE', 4096);


// KEY VALUE PAIR SETTINGS

define('IXG_KV_STORAGE', 'memcached'); # (memcached|raw) - Key-value helper to load for storage.
define('IXG_KV_SESSIONS', true); # Set this to false to use PHP's default session handler.
define('IXG_KV_SESSIONS_TIMEOUT', 3600 * 24); # Sets the amount of time before the user's session expires.
define('IXG_KV_URL_CACHE', true); # Helps improve speed by caching frequently used URL schemes.

# NOTE: It is highly advised that if IXG_KV_STORAGE is set to "raw",
# IXG_KV_SESSIONS is set to false. The raw storage will not automatically
# flush expired sessions.

define('IXG_MEMCACHED', 'localhost'); # Hostname for the Memcached server
define('IXG_MEMCACHED_PORT', 11211); # The port for the Memcached server
define('IXG_MEMCACHED_TYPE', 'MEMCACHED'); # (MEMCACHED|MEMCACHE) - Which PECL extension should be accessed

define('IXG_RAW', '../keyval.json'); # File path for raw key-value storage.