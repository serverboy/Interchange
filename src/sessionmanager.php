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
	private $session_id;
	private $session_data = array();
	
	public function __construct() {
		global $keyval;
		
		if(IXG_KV_SESSIONS) {
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
				
				setcookie('ixg_session', $session, (int)$_SERVER["REQUEST_TIME"] + IXG_KV_SESSIONS_TIMEOUT, '/');
				setcookie('ixg_token', $token, (int)$_SERVER["REQUEST_TIME"] + IXG_KV_SESSIONS_TIMEOUT, '/');
				
			}
			
			if($temp = $keyval->get('ixg_session/' . $session))
				$this->session_data = unserialize($temp);
			
		} else
			session_start();
		
	}
	public function destroy() {
		global $keyval;
		
		if(IXG_KV_SESSIONS) {
			$keyval->destroy('ixg_session/' . $this->session_id);
			setcookie('ixg_session', '', (int)$_SERVER["REQUEST_TIME"] + IXG_KV_SESSIONS_TIMEOUT, '/');
			setcookie('ixg_token', '', (int)$_SERVER["REQUEST_TIME"] + IXG_KV_SESSIONS_TIMEOUT, '/');
		} else
			session_destroy();
	}
	public function __set($name, $data) {
		global $keyval;
		if(IXG_KV_SESSIONS) {
			$this->session_data[$name] = $data;
			$keyval->set('ixg_session/' . $this->session_id, serialize($this->session_data));
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