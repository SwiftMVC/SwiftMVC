<?php
namespace Framework\Container;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * This class is a substitue for RequestMethods which gets the value from
 * PHP Globals using static methods
 */
class Request extends Base {

	/**
	 * @readwrite
	 * @var object Hold the symfony class object
	 */
	protected $_obj = null;

	public function __construct($opts = array()) {
		parent::__construct($opts);

		if (!$this->obj) {
			$this->obj = SymfonyRequest::createFromGlobals();
		}
	}

	public function queryBag() {
		return $this->obj->query;
	}

	public function postBag() {
		return $this->obj->request;
	}

	public function serverBag() {
		return $this->obj->server;
	}

	public function headerBag() {
		return $this->obj->headers;
	}

	/**
	 * Get Query
	 * @return string Escaped value
	 */
	public function get($key, $default = null) {
		$queryBag = $this->queryBag();
		if (array_key_exists($key, $queryBag->all())) {
			$val = $queryBag->get($key);
			return $this->escapeHtml($val);	
		}
		return $default;
	}

	/**
	 * Get a value from POST data
	 * @param  string $key     Name of the key
	 * @param  mixed $default Default value if key not found
	 * @return string          Escaped valued
	 */
	public function post($key, $default = null) {
		$postBag = $this->postBag();
		if (array_key_exists($key, $postBag->all())) {
			$val = $postBag->get($key);
			return $this->escapeHtml($val);	
		}
		return $default;
	}

	public function server($key, $default = null) {
		$serverBag = $this->serverBag();
		if (array_key_exists($key, $serverBag->all())) {
			$val = $serverBag->get($key);
			return $this->escapeHtml($val);	
		}
		return $default;
	}

	public function header($key, $default = null) {
		$headerBag = $this->headerBag();
		if (array_key_exists($key, $headerBag->all())) {
			$val = $headerBag->get($key);
			return $this->escapeHtml($val);
		}
		return $default;
	}

	public function jsonKey($key, $default = null) {
		$content = $this->obj->getContent();
		$arr = json_decode($content, true);
		if (isset($arr[$key])) {
			return $this->escapeHtml($arr[$key]);
		}
		return $default;
	}

	public function escapeHtml($val) {
		if (is_array($val)) {
			return $this->_escapeHtml($val);
		} else {
			return htmlspecialchars($val);
		}
	}

	protected function _escapeHtml($val) {
		$result = [];
		foreach ($val as $key => $value) {
			$key = $this->escapeHtml($key);
		    if (is_array($value)) {
		        $result[$key] = $this->_escapeHtml($value);
		    } else {
		        $result[$key] = $this->escapeHtml($value);
		    }
		}
		return $result;
	}

	public function path() {
		return $this->obj->getPathInfo();
	}

	public function getHost() {
		return $this->obj->getHttpHost();
	}

	public function getIp() {
		return $this->server('REMOTE_ADDR', '');
	}

	public function getPath() {
		return $this->obj->getRequestUri();
	}

	public function getMethod($lowerCase = false) {
		$method = $this->obj->getMethod();
		if ($lowerCase) {
			$method = strtolower($method);
		}
		return $method;
	}

	public function isMethod($method = 'GET') {
		$currentMethod = $this->getMethod(true);
		return strtolower($method) === $currentMethod;
	}

	public function isPost() {
		return $this->isMethod('post');
	}

	public function isDelete() {
		return $this->isMethod('delete');
	}

	public function isPatch() {
		return $this->isMethod('patch');
	}

	/**
	 * This function removes the HTML tags from a value
	 * @return mixed Sanitized value
	 */
	public function filterVar($data = '') {
		return trim(strip_tags($data));
	}
}
