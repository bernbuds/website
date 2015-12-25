<?php

class Bud extends Model
{
	protected $table = 'bud';
	protected $cols = array(
		'id'				=> 'i',
		'name'				=> 's', 
		'email'				=> 's', 
		'phone'				=> 's', 
		'zip'				=> 's', 
		'can_drive'			=> 'i', 
		'num_pickup'		=> 'i',
		'is_email_verified'	=> 'i',
		'is_phone_verified'	=> 'i',
		'email_code'		=> 's',
		'phone_code'		=> 's'
	);

	public static function FindByEmail($email)
	{
		return self::FindBy('email', $email);
	}

	public static function FindByPhone($phone)
	{
		return self::FindBy('phone', $phone);
	}
}