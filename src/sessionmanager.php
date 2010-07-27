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

class session_manager {
	private $use_mc = false;
	private $mc_type = 0; // 1 - Memcached, 2 - Memcache
	private $mc;
	private $session_id;
	private $session_data = array();
	
	public function __construct() {
		
		if(defined('IXG_MEMCACHED')) {
			if(class_exists("Memcached") && IXG_MEMCACHED_TYPE == "MEMCACHED")
				$this->mc_type = 1;
			elseif(class_exists("Memcache") && IXG_MEMCACHED_TYPE == "MEMCACHE")
				$this->mc_type = 2;
		}
		
		if(defined('IXG_MEMCACHED') && $this->mc_type > 0) {
			$this->use_mc = true;
			$ip = (getenv(HTTP_X_FORWARDED_FOR))
				?  getenv(HTTP_X_FORWARDED_FOR)
				:  getenv(REMOTE_ADDR);
			
			$has_session = false;
			
			if(isset($_COOKIE['ixg_session']) && isset($_COOKIE['ixg_token'])) {
				$session = $_COOKIE['ixg_session'];
				$token = $_COOKIE['ixg_token'];
				
				$hash = sha1($ip . $session . SECRET);
				if($hash == $token) {
					$has_session = true;
					$this->session_id = $session;
				}
				
			}
			
			if(!$has_session) {
				$session = uniqid();
				$token = sha1($ip . $session . SECRET);
				
				$this->session_id = $session;
				
				setcookie('ixg_session', $session, time() + 3600 * 24, '/');
				setcookie('ixg_token', $token, time() + 3600 * 24, '/');
				
			}
			
			if(IXG_MEMCACHED_TYPE=='MEMCACHED') {
				$memcache = new Memcached();
				$memcache->addServer(IXG_MEMCACHED, IXG_MEMCACHED_PORT) or die('Cannot connect to memcache');
			} else {
				$memcache = new Memcache();
				$memcache->connect(IXG_MEMCACHED, IXG_MEMCACHED_PORT) or die('Cannot connect to memcache');
			}
			
			$temp = '';
			if($temp = $memcache->get('ixg_session/' . $session))
				$this->session_data = unserialize($temp);
			
			$this->mc = $memcache;
		} else {
			session_start();
		}
	}
	public function destroy() {
		
		$this->mc->get('ixg_session/' . $this->session_id);
		
		setcookie('ixg_session', '', time() + 3600 * 24, '/');
		setcookie('ixg_token', '', time() + 3600 * 24, '/');
		
	}
	public function __set($name, $data) {
		if($this->use_mc) {
			$this->session_data[$name] = $data;
			$this->mc->set('ixg_session/' . $this->session_id, serialize($this->session_data));
		} else {
			$_SESSION[$name] = $data;
		}
	}
	public function __get($name) {
		if(isset($this->session_data[$name]))
			return $this->session_data[$name];
		else return false;
	}
}