<?php
namespace Shared;
use Framework\Registry;

class Utils {
	/**
	 * Capture the output of var_dump in a string and return it
	 * @param  mixed $var Variable to be debuged
	 * @return string
	 */
	public static function debugVar($var) {
		ob_start();
		var_dump($var);
		$result = ob_get_clean();
		return $result;
	}

	public static function flashMsg($msg) {
		$session = Registry::get("session");
		$session->set('$flashMessage', $msg);
	}
}