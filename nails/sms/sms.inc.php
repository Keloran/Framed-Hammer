<?php
/**
 * SMS
 *
 * @author Keloran
 */
class SMS {
	private $oNails		= false;
	//User details
	private $cUsername	= false;
	private $cPassword	= false;
	private $cFrom		= false;

	//Gateway settings
	private $cPrimaryGateway	= "http://www.intellisoftware.co.uk";
	private $cBackupGateway		= "http://www.intellisoftware2.co.uk";
	private $iMaxConCatMsgs 	= 1;

	/**
	 * SMS::__construct
	 *
	 * @param object $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails = $oNails;

		//Get the username/password/who from {e.g. geoff | password | Hammer}
		$this->cUsername	= $this->oNails->getConfig("sms", "username");
		$this->cPassword	= $this->oNails->getConfig("sms", "password");
		$this->cFrom		= $this->oNails->getConfig("sms", "from");
	}

	/**
	 * SMS::sendMessage
	 *
	 * @desc Send a message to the user
	 * @param string $cMessage The message to send to the user
	 * @param digit $dPhone The phone number of the user to send the message to
	 * @return bool
	 */
	public function sendMessage($cMessage, $dPhone) {
		$aFormParams = array(
			'username'		=>	$this->cUsername,
			'password'		=>	$this->cPassword,
			'to'			=>	$dPhone,
			'from'			=>	$this->cFrom,
			'text'			=>	$cMessage,
			'maxconcat'		=>	$this->iMaxConCatMsgs
		);

		$cFormData 				= http_build_query($aFormParams, '', '&');
		$oResponse 				= $this->makeHTTPRequest($cFormData);
		$oSendStatusCollection	= $this->parseHTTPResponse($oResponse, $dPhone);

		return $oSendStatusCollection;
	}

	/**
	 * SMS::getBalance
	 *
	 * @desc Get the balance for the admin
	 * @return int
	 */
	public function getBalance() {
		$aFormParams = array(
			'username'	=>	$this->cUsername,
			'password'	=>	$this->cPassword
		);

		$cFormData	= http_build_query($aFormParams, '', '&');
		$oResponse	= $this->makeHTTPRequestToURL($cFormData, "/smsgateway/getbalance.aspx");
		$iResults	= $this->parseHTTPResponse_GetBalance($oResponse);

		return $iResults;
	}

	/**
	* SMS::makeHTTPRequest()
	*
	* @desc Make the request using HTTP
	* @param string $cData The whole data string
	* @return bool
	*/
	private function makeHTTPRequest($cData) {
		return $this->makeHTTPRequestToURL($cData, "/smsgateway/sendmsg.aspx");
	}

	private function makeHTTPRequestToURL($cData, $cURL) {
		try {
			$mResponse = $this->makeHTTPRequestUsingGateway(1, $cData, $cURL);
		} catch (Spanner $e) {
			try {
				//Try backup gateway SMSGateway
				$mResponse = $this->makeHTTPRequestUsingGateway(2, $cData, $cURL);
			} catch (Spanner $e_b) {
				//Throw first exception
				throw $e_b;
			}
		}

		return $mResponse;
	}

	private function makeHTTPRequestUsingGateway($iGatewayID, $cData, $cURL) {
		if ($iGatewayID == 1) {
			$cGateway = $this->cPrimaryGateway . $cURL;
		} else if ($iGatewayID == 2) {
			$cGateway = $this->cBackupGateway . $cURL;
		} else {
			throw new Spanner("Gateway Id invalid " . $iGatewayID);
		}

		return $this->makeHTTPFormPost($cURL, $cData, "Content-Type: application/x-www-form-urlencoded\r\n");
	}


	private function parseHTTPResponse($mResponse, $dTo) {
		$cIDPrefix		= "ID:";
		$cErrorPrefix	= "ERR:";

		$aSendStatusCollection	= array();
		$aResponses				= explode("\n", $mResponse);
		$i						= 0;

		foreach($aResponses as $cResponse) {
			$cResponse = trim($cResponse);

			if (strlen($cResponse[1])) {
				$aParts = explode(",", $cResponse);

				$msisdn			= null;
				$msgid			= null;
				$errorstatus	= null;

				if (isset($aParts[1])) {
					$msisdn  	= $aParts[0];
					$msgresult	= $aParts[1];
				} else {
					if(count(explode(",", $to)) == 1) {
						$msisdn = $to;
					} else {
						$msisdn = "";
					}
					$msgresult = $aParts[0];
				}

				if (strncmp($msgresult, $const_IdPrefix, strlen($const_IdPrefix)) == 0) {
					$msgid = substr($msgresult,strlen($const_IdPrefix));
					$errorstatus = "OK";
				} else if (strncmp($msgresult, $const_ErrPrefix, strlen($const_ErrPrefix))==0 ) {
					$msgid = "NoId";
					$errorstatus = substr($msgresult,strlen($const_ErrPrefix));
				}

				$SendStatusCollection[$idx]["To"] = $msisdn;
				$SendStatusCollection[$idx]["MessageId"] = $msgid;
				$SendStatusCollection[$idx]["Result"] = $errorstatus;

				$idx++;
			}
		}

		return $SendStatusCollection;
	}


	private function parseHTTPResponse_GetBalance($mResponse) {
		$const_BalancePrefix = "BALANCE:";
		$const_ErrPrefix = "ERR:";

		$aResults = array();

		if (strncmp($mResponse, $const_BalancePrefix, strlen($const_BalancePrefix)) == 0) {
			$aResults["Balance"] 		= substr($mResponse, strlen($const_BalancePrefix));
			$aResults["ErrorStatus"]	= "OK";
		} else if (strncmp($mResponse, $const_ErrPrefix, strlen($const_ErrPrefix)) == 0) {
			$aResults["Balance"] 		= -1;
			$aResults["ErrorStatus"]	= substr($mResponse, strlen($const_ErrPrefix));
		}

		return $Results;
	}


	private function MakeHTTPFormPost ( $url, $data, $optional_headers = null) {
			 $params = array ( 'http' => array ( 'method' => 'POST', 'content' => $data ) );

			 if ($optional_headers !== null) {
				$params['http']['header'] = $optional_headers;
			 }

			 $ctx = stream_context_create($params);

			 $fp = @fopen($url, 'rb', false, $ctx);
			 if (!$fp) {
				throw new Exception("Problem making HTTP request $url, $php_errormsg");
			 }

			 $response = @stream_get_contents($fp);
			 if ($response === false) {
				throw new Exception("Problem reading HTTP Response $url, $php_errormsg");
			 }

			 return $response;
		}
}
