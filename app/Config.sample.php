<?php

class Config
{
	protected static $config = array();

	public static function init()
	{
		self::$config['info'] = array(
			'domain'	=> 'bernbuds.net'
		);

		self::$config['db'] = array(
			'host'      => '',
			'username'  => '',
			'password'  => '',
			'table'     => '',
		);

		self::$config['captcha'] = array(
			'site_key'      => '',
			'secret_key'    => ''
		);

		self::$config['twilio'] = array(
			'sid'       => '',
			'token'     => '',
			'from_num'  => ''
		);

		self::$config['mailgun'] = array(
			'from_name'	=> 'BernBuds',
			'from_email'=> 'bud@bernbuds.net',
			'secret'    => '',
			'public'    => ''
		);
	}

	public function get($what)
	{
		$bits = explode('/', $what);

		$res = self::$config[$bits[0]];

		for($i = 1; $i < count($bits); $i++) {
			$key = $bits[$i];
			$res = $res[$key];
		}

		return $res;
	}
}

Config::init();

?>