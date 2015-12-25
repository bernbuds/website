<?php

class QueryException extends Exception {};

class DB
{
	protected static $instance;

	protected $mysqli;
	protected $last_prepared_query;

	public $debug = false;

	public function __construct()
	{
		$db = Config::get('db');
		$this->mysqli = new mysqli($db['host'], $db['username'], $db['password'], $db['table']);
		
		if ($this->mysqli->connect_error) {
			throw new Exception('Connect Error (' . $this->mysqli->connect_errno . ') ' . $this->mysqli->connect_error);
		}
	}

	public static function instance()
	{
		return self::$instance ?: (self::$instance = new DB());
	}

	// Execute a MySQLi stmt
	public function execute($stmt)
	{
		if($this->debug) {
			echo "\nEXECUTE: $this->last_prepared_query\n\n";
		}

		if(!$stmt->execute()) {
			throw new QueryException("Error executing ($this->last_prepared_query): " . $this->mysqli->error);
		}
	}

	public function prepare($query)
	{
		if( !($stmt = $this->mysqli->prepare($query)) ) {
			throw new QueryException("Error preparing ($query): " . $this->mysqli->error);
		}

		$this->last_prepared_query = $query;
		return $stmt;
	}

	// pass unknown object probing to the mysqli instance
	public function __get($attr)
	{
		return $this->mysqli->$attr;
	}

	public function __set($attr, $val)
	{
		$this->mysqli->$attr = $val;
	}

	public function __call($method, $args)
	{
		return call_user_func_array($this->mysqli, $args);
	}
}