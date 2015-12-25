<?php

require_once(__DIR__ . '/../boot.php');

class Controller
{
	public function __construct()
	{
		// public methods in this controller are callable from the uri
		$this->callMethod();
	}

	protected function callMethod()
	{
		if(!$method = @$_GET['action']) {
			return false;
		}

		if(!in_array($method, $this->getPublicMethods())) {
			die("bad action: $method");
		}

		$params = @is_array($_GET['params']) ? $_GET['params'] : [];
		call_user_func_array(array($this, $method),  $params);
	}

	// https://secure.php.net/manual/en/function.get-class-methods.php
	protected function getPublicMethods() 
	{
		$public_methods = array();

		foreach (get_class_methods($this) as $method) {

			$reflect = new ReflectionMethod($this, $method);

			/* For private, use isPrivate().  For protected, use isProtected() */
			/* See the Reflection API documentation for more definitions */
			if($reflect->isPublic()) {
				array_push($public_methods,$method);
			}
		}

		/* return the array to the caller */
		return $public_methods;
	}
}