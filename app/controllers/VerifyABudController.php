<?php

require_once(__DIR__ . '/APIController.php');

class VerifyABudController extends APIController
{
	public function verifyContact()
	{
		$bud = Bud::FindByEmail(@$_POST['email']);

		if(@$_POST['verify_email'])
		{
			if($_POST['verify_email'] != $bud->email_code) {
				$this->api_die('Wrong email verification code, try again');
			}

			$bud->is_email_verified = 1;
		}

		if(@$_POST['verify_phone'])
		{
			if($_POST['verify_phone'] != $bud->phone_code) {
				$this->api_die('Wrong phone verification code, try again');
			}

			$bud->is_phone_verified = 1;
		}

		$bud->save();
		$this->api_success();
	}
}

$handler = new VerifyABudController();

?>