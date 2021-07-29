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
	// http://reference.elgg.org/classElgg_1_1Email_1_1Attachment.html
	// https://docs.aws.amazon.com/ses/latest/DeveloperGuide/examples-send-raw-using-sdk.html

	private static $usePHPmailerAlways = true;
	private static $sendErrorMessage = true;
	
	public static function transport(\Elgg\Hook $hook) {
	
		$plugin = \elgg_get_plugin_from_id('amazon_ses');
		
		$email = $hook->getParam('email');

		$from = $email->getFrom()->getEmail();
		$fromName = $email->getFrom()->getName();
		$to = $email->getTo()->getEmail();
		$subject = $email->getSubject();
		$message = $email->getBody();
		$elgg_attachments = $email->getAttachments();

		$sesClient = SesClient::factory([
			'version'=> 'latest',
			'region' => $plugin->AWS_REGION
		]);

		try {

			$attachments = self::prepareAttachments($elgg_attachments);
			if($attachments || self::$usePHPmailerAlways){

				$raw = self::getRawMessage($from,$fromName,$to,$subject,$message,$attachments);
				if($raw){

					$result = $sesClient->sendRawEmail([
						"RawMessage" => [
							"Data" => $raw
						]
					]);

				}else{

					return false;
				}

			}else{

				$result = $sesClient->sendEmail(
					self::getSesEmailProperties($from,$fromName,$to,$subject,$message)
				);
			}

			if($result && $result->get('MessageId')){

				return true;
			}
			
		} catch (SesException $error) {
			
			if(self::$sendErrorMessage){

				$errorMessage = $error->getAwsErrorMessage();
			
				$message .= ' ..... send by mail.';
		 
				$headers = "From: $from"; 
				if(\mail($to,$subject,$message,$headers) !== false){
					return true;
				}   
			}         
		}
		
		return false;
	}

	private static function getSesEmailProperties($from, $from_name, $to, $subject, $message){

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

		return [
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
		];
	}

	private static function getRawMessage($from, $from_name, $to, $subject, $message, $attachments = Array()){

		$body = Array();
		if(Ses::isHTMLType($message)){
			
			$body['Html'] = $message;
			$body['Text'] = Ses::freeHTMLMessage($message);
			
		}else{
			
			$body['Html'] =  $message;
			$body['Text'] =  $message;
		}

		// Create PHPMailer instance
		$mail = new \PHPMailer\PHPMailer\PHPMailer();

		// Set the email values
		$mail->setFrom($from, $from_name);
		$mail->addAddress($to);
		$mail->Subject = $subject;
		$mail->Body = $body['Html'];
		$mail->AltBody = $body['Text'];

		// Here you attach your file or files
		// @string path
		// @string file_name. If not specified, it takes the original name from the path.
		foreach($attachments as &$attachment){

			$mail->addAttachment($attachment['path'], $attachment['name']);
		}
		
		//$mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configset);

		// Attempt to assemble the above components into a MIME message.
		if (!$mail->preSend()) {
			return null;
		} else {
			return $mail->getSentMIMEMessage();
		}
	}

	private static function prepareAttachments($attachments = Array()){

		$attch = Array();
		if($attachments){

			foreach($attachments as &$attachment){

				$tempFile = tmpfile();
				$metaData = stream_get_meta_data($tempFile);
				$filepath = $metaData['uri']; // Check /tmp Permissions
				
				file_put_contents($filepath, $attachment->getRawContent());

				$attch[] = Array(
					'path' => $filepath,
					'name' => $attachment->filename,
				);
			}
		}

		return $attch;
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
