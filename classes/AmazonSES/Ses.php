<?php

namespace AmazonSES;

use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;

/**
 * Base
 */
class Ses {
	
	// http://learn.elgg.org/en/3.2/guides/hooks-list.html#emails
	// http://reference.elgg.org/interfaceElgg_1_1Hook.html
	// http://reference.elgg.org/classElgg_1_1Email.html
	
	public static function transport(\Elgg\Hook $hook) {
	
		$plugin = \elgg_get_plugin_from_id('amazon_ses');
		
		$email = $hook->getParam('email');

		$to = $email->getTo()->getEmail();
		$from = $email->getFrom()->getEmail();
		$subject = $email->getSubject();
		$message = $email->getBody();

		$client = SesClient::factory([
			'version'=> 'latest',
			'region' => $plugin->AWS_REGION
		]);

		$body = Array();
		if(Ses::isHTMLType($message)){
			
			$body['Html'] = Array(
				'Charset' => 'UTF-8',
				'Data' => $message,
			);
			
			$body['Text'] = Array(
				'Charset' => 'UTF-8',
				'Data' => Ses::freeHTMLMessage($message),
			);
			
		}else{
			
			$body['Text'] = Array(
				'Charset' => 'UTF-8',
				'Data' => $message,
			);
		}

		try {

			$result = $client->sendEmail([
				'Destination' => [
					'ToAddresses' => [
						$to,
					],
				],
				'Message' => [
					'Body' => $body,
					'Subject' => [
						'Charset' => 'UTF-8',
						'Data' => $subject,
					],
				],
				'Source' => $from, 
			]);
			
			$messageId = $result->get('MessageId');

			return true;
			
		} catch (SesException $error) {
			
			$errorMessage = $error->getAwsErrorMessage();
			
			$message .= ' ..... send by mail.';
	 
			$headers = "From: $from"; 
			if(\mail($to,$subject,$message,$headers) !== false){
				return true;
			}               
		}
		
		return false;
	}

	public static function freeHTMLMessage($html){

		$nonHTML = \preg_replace('/<.*?>/s', '', $html);
		// Replace non-breaking space with regular space
		$messagePlain = \preg_replace('/&nbsp;/', ' ', $nonHTML);
		
		return $messagePlain;

	}

	public static function isHTMLType($message){
		
		if(\preg_match('/<.*?>/s',$message)){
			
			return 'Html';
		}
		
		return 'Text';
	}
	
}