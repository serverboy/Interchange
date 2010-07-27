<?php

/*
Basic XOR Encryption Library

Note: This library is not secure for handling large blocks of ciphertext. This
library should only be used for one-time pads where the key length is very
close to the length of the ciphertext (or where the key is hashed with
a one-time-use string). Do not use this without first studying other forms of
encryption.


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