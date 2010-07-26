<?

/*
*
*	XOR Encryption Library
*
*	Note: This library is not secure for handling large blocks of ciphertext. This
*	library should only be used for one-time pads where the key length is very
*	close to the length of the ciphertext (or where the key is hashed with
*	a one-time-use string). Do not use this without first studying other forms of
*	encryption.
*
*/

class lib_encrypt_xor {
	
	private function runXOR($InputString, $KeyPhrase){
	 
	    $KeyPhraseLength = strlen($KeyPhrase);
	 
	    // Loop trough input string
	    for ($i = 0; $i < strlen($InputString); $i++){
	 
	        // Get key phrase character position
	        $rPos = $i % $KeyPhraseLength;
	 
	        // Magic happens here:
	        $r = ord($InputString[$i]) ^ ord($KeyPhrase[$rPos]);
	 
	        // Replace characters
	        $InputString[$i] = chr($r);
	    }
	 
	    return $InputString;
	}
	
	function encrypt($data, $key) {
		$data = $this->runXOR($data, $key);
		$data = base64_encode($data);
		return $data;
	}
	function decrypt($data, $key) {
		$data = base64_decode($data);
		$data = $this->runXOR($data, $key);
		return $data;
	}
	
}