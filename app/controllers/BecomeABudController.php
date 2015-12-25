<?php

require_once(__DIR__ . '/APIController.php');

class BecomeABudController extends APIController
{
	public function contactFormSubmission()
	{
		$this->validateContactForm($_POST);
		$bud_id = $this->saveToDB();

		$sms_res 	= $this->sendVerificationCode($bud_id, 'email', $_POST['email']);
		$email_res 	= $this->sendVerificationCode($bud_id, 'phone', $_POST['phone']);

		$this->api_success(array(
			'bud_id'	=> $bud_id,
		//	'sms_res'	=> $sms_res,
		//	'email_res'	=> $email_res
		));
	}

	protected function saveToDB()
	{
		$bud = new Bud();
		$bud->name 				= $_POST['name'];
		$bud->email 			= $_POST['email'];
		$bud->phone 			= $_POST['phone'];
		$bud->zip 				= $_POST['zip'];
		$bud->can_drive 		= isset($_POST['can_drive']) ? $_POST['can_drive'] : 0 ;
		$bud->num_pickup 		= $_POST['num_pickup'] ? $_POST['num_pickup'] : 0 ;
		$bud->is_email_verified = 0;
		$bud->is_phone_verified = 0;

		return $bud->save();
	}

	protected function sendVerificationCode($bud_id, $email_or_phone, $where)
	{
		$code = rand(1000,9999);
		$bud = new Bud($bud_id);

		if( $email_or_phone == 'phone' ) 
		{
			$bud->phone_code = $code;
			$bud->save();

			try {
				return $this->sendSMS($where, $code);
			}
			catch(Services_Twilio_RestException $e) {
				$this->api_die($e->getMessage());
			}
		}

		else if($email_or_phone == 'email')
		{
			$bud->email_code = $code;
			$bud->save();

			$mailer = new \email\Mailer();
			return $mailer->sendVerificationEmail($where, $code);
		}
	}

	protected function sendSMS($where, $what)
	{
		$client = new Services_Twilio(Config::get('twilio/sid'), Config::get('twilio/token'));
		$message = $client->account->messages->sendMessage(
			Config::get('twilio/from_num'), $where,
			"Your BernBuds phone validation code is: $what"
		);

		return $message->sid;
	}

	protected function validateContactForm($data) 
	{
		// $this->validateCaptcha($data);

		if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			$this->api_die('Please enter a valid email');
		}

		if(isset($data['can_drive'])) 
		{
			if($data['can_drive'] < 0 || $data['can_drive'] > 1) {
				$this->api_die('Error with can_drive');
			}

			if( $data['can_drive'] == 1 && !$data['num_pickup'] ) {
				$this->api_die('Please tell us how many other BernBuds you can pick up');
			}
		}
		
		if(isset($data['num_pickup'])) {
			if($data['num_pickup'] > 9 || $data['num_pickup'] < 0 ) {
				$this->api_die('We limit pickup to 9 other BernBuds');
			}
		}

		// todo: regexp zip
		if( !preg_match('/\d{5}(-\d{4})?/', $data['zip']) ) {
			$this->api_die('Please enter a valid zip code');
		}

		// clean & test phone (@hacky)
		$_POST['phone'] = $data['phone'] = preg_replace('/[^0-9]/', '', $data['phone']);
 
		if( !preg_match('/1?\d{9}/', $data['phone']) ) {
			$this->api_die('Please enter a valid phone number');
		}
		
		// todo: search for duplicate phone numbers
		$this->validateNotExisting('phone', $data['phone']);

		// search for duplicate email addresses 
		$this->validateNotExisting('email', $data['email']);
	}

	protected function validateCaptcha($data)
	{
		if(!$captcha_response = @$data['g-recaptcha-response']) {
			$this->api_die('Try the captcha again');
		}

		$captcha_secret = Config::get('captcha/secret_key');
		$verify_url = "https://www.google.com/recaptcha/api/siteverify?secret=$captcha_secret&response=$captcha_response&remoteip=$_SERVER[HTTP_X_FORWARDED_FOR]";
		$response = json_decode(file_get_contents($verify_url), true);
		
		if(!$response['success'])
		{
			$this->api_die('The captcha was incorrect');
		}
	}

	protected function validateNotExisting($email_or_phone, $val)
	{
		try {
			$bud = Bud::FindBy($email_or_phone, $val);

			// has this user already been verified?
			if( $bud->is_email_verified == 1 || $bud->is_phone_verified == 1 ) {
				$this->api_die("That $email_or_phone is already registered");
			}

			// if it hasn't been verified yet, just delete the bud to recreate him later
			$bud->delete();
		}
		catch(ModelNotFoundException $e) {}
	}
}

$bud_handler = new BecomeABudController();

?>