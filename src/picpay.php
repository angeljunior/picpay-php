<?php 

namespace angeljunior;


/**
 * PicPay-PHP V1
 *
 * TERMS OF USE:
 * - This code is in no way affiliated with, authorized, maintained, sponsored
 *   or endorsed by PicPay or any of its affiliates or subsidiaries. This is
 *   an independent and unofficial API. Use at your own risk.
 * - We do NOT support or tolerate anyone who wants to use this API to send spam
 *   or commit other online crimes.
 *
 */


class PicPay {


	public static $APIPayment = 'https://appws.picpay.com/ecommerce/public/payments';
	 

	private static $urlCallBack;
	 
  	private static $urlReturn;
	 
  	private static $xPicPayToken;
	  
	private static $xSellerToken;


	public function __construct($x_picpay_token = null,
								$x_seller_token = null,
								$url_callback 	= null,
								$url_return 	= null) {


		if (!is_string($x_picpay_token) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $x_picpay_token) !== 1)) {
    		throw new Exception("Seu x-pipay-token é inválido.", 1);
		}


		if (!is_string($x_seller_token) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $x_seller_token) !== 1)) {
    		throw new Exception("Seu x-seller-token é inválido.", 1);
		}


		self::$xPicPayToken = $x_picpay_token,
		self::$xSellerToken = $x_seller_token,

		if(!is_null($url_callback) && filter_var($url_callback, FILTER_VALIDATE_URL)) {
			self::$urlReturn = $url_return;
		}

		if(!is_null($url_callback) && filter_var($url_callback, FILTER_VALIDATE_URL)) {
			self::$urlCallBack = $url_callback;
		}



	}
	   
	   
	 
 	public function createPayment($item,$buyer){


 		if(empty(self::$urlCallBack) || is_null(self::$urlCallBack))
 			throw new Exception("Não foi definida uma URL para o Callback do pagamento.", 1);

 		if(empty(self::$urlReturn) || is_null(self::$urlReturn))
 			throw new Exception("Não foi definida uma URL para o retorno do comprador.", 1);

		 
	  	$data = [
	  		'callbackUrl' 	=> self::$urlCallBack,
	        'returnUrl'   	=> self::$urlReturn,
	        'referenceId' 	=> $item->referenceID,
	        'value'       	=> $item->amount,
	        'purchaseMode' 	=> 'online',
        	'buyer'       	=> [
				'firstName' 	=> $buyer->name,
				'document'  	=> $buyer->cpf,
				'email'     	=> $buyer->email
			]
		];
		 
		$ch = curl_init(self::$APIPayment);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		 curl_setopt($ch, CURLOPT_HTTPHEADER, ['x-picpay-token: '.self::$xPicPayToken)];
		 
		 $res = curl_exec($ch);
		 curl_close($ch);
	     $return = json_decode($res);
		 
		 return $return;
		  		  
	}
	 
	 
	 
	public function notificationPayment(){
		 
		$content = trim(file_get_contents("php://input"));
	    $payBody = json_decode($content);
		 
		if(isset($payBody->authorizationId)):
		   
		   $referenceId = $payBody->referenceId; 
		 
		   $ch = curl_init('https://appws.picpay.com/ecommerce/public/payments/'.$referenceId.'/status');
		   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		   curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-picpay-token: '.self::$xPicPayToken)); 
		
		   $res = curl_exec($ch);
		   curl_close($ch);
		   $notification = json_decode($res); 
		  		   
		   $notification->referenceId     = $payBody->referenceId; 
		   $notification->authorizationId = $payBody->authorizationId;
		   
		   return $notification;
		   
		 else:
		  
			return false;
		  
		 endif;
		  
		 
	}
	 

	 
	 
  }


  
  
  
?>
