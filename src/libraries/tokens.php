<?

/*
URL Suffixing

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

class lib_tokens {
	function exec($data) {
		$data;
		return false;
	}
	
	function URLSuffix($token, $id='', $amp='&') {
		global $uid;
		
		$timestamp = time();
		$elements = array(
			'token_key'=>sha1($uid . $timestamp . 'binding'),
			'timestamp'=>$timestamp
		);
		
		$serial = $token->serialize();
		$serial = getLib('encrypt_xor')->encrypt($serial, sha1($uid . $timestamp));
		
		$elements['token'] = $serial;
		
		return http_build_query($elements, $id.'_', $amp);
	}
	
	function retrieve($id='') {
		global $uid, $db;
		
		if(!empty($id))
			$id .= '_';
		
		if(	!isset($_REQUEST[$id.'token_key']) ||
			!isset($_REQUEST[$id.'timestamp']) ||
			!isset($_REQUEST[$id.'token']))
			return false;
		
		$timestamp = intval($_REQUEST[$id.'timestamp']);
		if(time() - $timestamp > 3600)
			return false;
		$sig = sha1($uid . $timestamp . 'binding');
		if($sig != $_REQUEST[$id.'token_key'])
			return false;
		
		$serial = getLib('encrypt_xor')->decrypt(trim($_REQUEST[$id.'token']), sha1($uid . $timestamp));
		$token = $db->unserializeToken($serial);
		
		return $token;
		
	}
	
}