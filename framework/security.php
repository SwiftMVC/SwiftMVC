<?php
namespace Framework;

/**
 * This is a class which hashes the string for storing in the database and
 * also considering the Timing Leaks
 * Reference: https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
 *
 * Dependencies ------> php-mcyrpt, php-xml
 */
class Security {
	protected static $_algos = ['sha256', 'sha384'];

	protected static function _verifyAlgo($algo) {
		if (!in_array($algo, self::$_algos)) {
			throw new \Exception("Invalid Second argument algo");
		}
	}

	public static function generateToken($length = 20) {
		return bin2hex(random_bytes($length));
	}

	public static function hashStr($str, $algo = 'sha384') {
		self::_verifyAlgo($algo);

		return password_hash(
		    base64_encode(
		        hash($algo, $str, true)
		    ),
		    PASSWORD_DEFAULT
		);
	}

	public static function verifyHash($hashStr, $plainStr, $algo = 'sha384') {
		self::_verifyAlgo($algo);

		return password_verify(
		    base64_encode(
		        hash($algo, $plainStr, true)
		    ),
		    $hashStr
		);
	}

	public static function encrypt($data, $key) {
		$e = new Security\Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
		$hashed = $e->encrypt($data, $key);
		
		return utf8_encode($hashed);
	}

	public static function decrypt($data, $key) {
		$data = utf8_decode($data);
		$e = new Security\Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
		$normal = $e->decrypt($data, $key);

		return $normal;
	}
}
