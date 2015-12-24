<?php

class Encryption {
	private $key;
	private $blocksize;

	public function __construct($key) {
		$this->key = $key;
		$this->blocksize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
	}

	public function encrypt_aes_ecb_pkcs5($payload) {
		$payload = self::pkcs5_pad($payload, $this->blocksize);
		$encrypted = mcrypt_encrypt(
			MCRYPT_RIJNDAEL_128,
			$this->key,
			$payload,
			MCRYPT_MODE_ECB
		);
		return self::base64url_encode($encrypted);
	}

	// From http://php.net/manual/en/ref.mcrypt.php#69782
	public static function pkcs5_pad ($text, $blocksize) {
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}

	// From http://php.net/manual/en/ref.mcrypt.php#69782
	public static function pkcs5_unpad($text) {
		$pad = ord($text{strlen($text)-1});
		if ($pad > strlen($text)) {
			return false;
		}
		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
			return false;
		}
		return substr($text, 0, -1 * $pad);
	}

	public static function base64url_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	public static function base64url_decode($data) {
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}
}