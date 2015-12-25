<?php

namespace email;
use Mailgun\Mailgun;
use Config;

class Mailer
{
	public function sendVerificationEmail($where, $what)
	{
		# First, instantiate the SDK with your API credentials and define your domain. 
		$msg = new Mailgun(Config::get('mailgun/secret'));
		$domain = Config::get('info/domain');

		# Now, compose and send your message.
		$msg->sendMessage($domain, array(
			'from'    => Config::get('mailgun/from_email'), 
			'to'      => $where, 
			'subject' => "Your BernBuds email validation code is: $what", 
			'text'    => "Your BernBuds email validation code is: $what")
		);

		$result = $msg->get("$domain/log", array(
			'limit' => 1, 
			'skip'  => 0
		));

		$httpResponseCode = $result->http_response_code;
		$httpResponseBody = $result->http_response_body;

		return $httpResponseBody;
	}
}