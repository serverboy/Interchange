<?php

/*
Serverboy Interchange
Session management

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

class memcached_driver {
	//private $mc_type = 2; // 1 - Memcached, 2 - Memcache
	private $mc;
	
	public function __construct() {
		/*
		if(class_exists("Memcached") && IXG_MEMCACHED_TYPE == "MEMCACHED")
			$this->mc_type = 1;
		elseif(class_exists("Memcache") && IXG_MEMCACHED_TYPE == "MEMCACHE")
			$this->mc_type = 2;
		*/
		switch(IXG_MEMCACHED_TYPE) {
			case 'MEMCACHED':
				$this->mc = new Memcached();
				$this->mc->addServer(IXG_MEMCACHED, IXG_MEMCACHED_PORT) or die('Cannot connect to memcache');
				break;
			default:
				$this->mc = new Memcache();
				$this->mc->connect(IXG_MEMCACHED, IXG_MEMCACHED_PORT) or die('Cannot connect to memcache');
		}
	}
	public function destroy($id) {
		$this->mc->delete($id);
	}
	public function set($name, $data, $expiration = 0) {
		$this->mc->set($name, $data, 0, $expiration);
	}
	public function get($name) {
		return $this->mc->get($name);
	}
	public function increment($name) {
		return $this->mc->increment($name);
	}
	public function end() {$this->mc->close();}
}