<?php 


class PicPay {


	public static $APIPayment = 'https://appws.picpay.com/ecommerce/public/payments';
	public static $APIReference = 'https://appws.picpay.com/ecommerce/public/payments/@referenceId/status';
	 

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


		self::$xPicPayToken = $x_picpay_token;
		self::$xSellerToken = $x_seller_token;

		if(!is_null($url_callback) && filter_var($url_callback, FILTER_VALIDATE_URL)) {
			self::$urlReturn = $url_return;
		}

		if(!is_null($url_callback) && filter_var($url_callback, FILTER_VALIDATE_URL)) {
			self::$urlCallBack = $url_callback;
		}



	}
	   
	   
	 
 	public function createPayment($item, $buyer){


 		if(empty(self::$urlCallBack) || is_null(self::$urlCallBack))
 			throw new Exception("Não foi definida uma URL para o Callback do pagamento.", 1);

 		if(empty(self::$urlReturn) || is_null(self::$urlReturn))
 			throw new Exception("Não foi definida uma URL para o retorno do comprador.", 1);

		 
	  	$data = [
	  		'callbackUrl' 	=> self::$urlCallBack,
	        'returnUrl'   	=> self::$urlReturn,
	        'referenceId' 	=> (string)$item->referenceID,
	        'value'       	=> (float)$item->amount,
	        'purchaseMode' 	=> 'online',
        	'buyer'       	=> [
				'firstName' 	=> $buyer->name,
				'document'  	=> $buyer->cpf,
				'email'     	=> $buyer->email
			]
		];
		 
		$ch = curl_init(self::$APIPayment);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['x-picpay-token: '.self::$xPicPayToken]);
		 
		$response = curl_exec($ch);
		curl_close($ch);

	    return json_decode($response, 1);
		  		  
	}
	 
	 
	 
	public function getPayment($referenceID){
		 
		 
		   $ch = curl_init(str_replace('@referenceId', $referenceID, self::$APIReference));
		   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		   curl_setopt($ch, CURLOPT_HTTPHEADER, ['x-picpay-token: '.self::$xPicPayToken]); 
		
		   $response = curl_exec($ch);
		   curl_close($ch);
		   
		   return json_decode($response, 1); 
		 
	}
	 

	 
	 
}

  
?>