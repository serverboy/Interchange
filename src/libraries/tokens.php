<?

/*
*
*	Token Library
*
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