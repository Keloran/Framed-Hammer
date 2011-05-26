<?php
class Email_Send {
	private $aParams;

	private static $oSend;

	/**
	 * Email_Send::__construct()
	 *
	 */
	function __construct() {
		//set the inital boundary, can be reset by the user
		$this->setBoundry();

		//set the inital agent, can be reset by the user
		$this->setAgent();
	}

	/**
	 * Email_Send::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->aParams[$cName] = $mValue;
	}

	/**
	 * Email_Send::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		$mReturn = false;

		if (isset($this->aParams[$cName])) {
			$mReturn = $this->aParams[$cName];
		}

		return $mReturn;
	}

	/**
	 * Email_Send::__isset()
	 *
	 * @param string $cName
	 * @return bool
	 */
	public function __isset($cName) {
		$bReturn = false;

		if (isset($this->aParams[$cName])) {
			$bReturn = true;
		}

		return $bReturn;
	}

	/**
	 * Email_Send::setBoundry()
	 *
	 * @param string $cBoundry
	 * @return string
	 */
	public function setBoundry($cBoundry = false) {
		$cReturn = "----";

		if (!$cBoundry) {
			$cReturn .= "Hammer_Mailer";
		} else {
			$cReturn .= $cBoundry;
		}

		$cReturn .= "----";
		$cReturn .= md5(time());

		$this->cBoundry = $cReturn;
		return $cReturn;
	}

	/**
	 * Email_Send::setAgent()
	 *
	 * @param string $cAgent
	 * @return string
	 */
	public function setAgent($cAgent = false) {
		if (!$cAgent) { $cAgent = "Hammer Mailer"; }

		$this->cAgent  = "X-Mailer: " . $cAgent . "\r\n";
		$this->cAgent .= "User-Agent: " . $cAgent . "\r\n";

		return $this->cAgent;
	}

	/**
	 * Email_Send::getInstance()
	 *
	 * @return object
	 */
	public static function getInstance() {
		if (is_null(self::$oSend)) {
			self::$oSend = new Email_Send();
		}

		return self::$oSend;
	}

	/**
	 * Email_Send::compose()
	 *
	 * @return
	 */
	public function compose($aMessage) {
		$bTemplate		= false; //if your using a template this will get set to true
		$cTemplate		= false; //the template name
		$aParams		= false;
		$mAttachments	= false;

		//if its not an array dont do anything
		if (!is_array($aMessage)) { return false; }

		//set this to true since your using the new method,
		//if you want html you will have added it in the array
		$bTextOnly	= true;
		foreach ($aMessage as $cKey => $mValue) {
			switch ($cKey) {
				//Who to
				case "to":
					$this->cTo = $mValue;
					break;

				//Subject
				case "title":
				case "subject":
					$this->cSubject = $mValue;
					break;

				//html part of the email
				case "content":
				case "html":
					$this->html($mValue);
					break;

				//text of the email
				case "contentText":
				case "text":
					$this->text($mValue);
					break;

				//template thats to be used instead of written html
				case "template":
					$this->setTemplate($mValue);
					break;

				//template params
				case "templateParams":
				case "params":
					$this->setTemplateParams($mValue);
					break;

				//From address
				case "from":
					$this->cFrom = $mValue;
					break;

				//From name e.g. site.com
				case "fromName":
				case "fromname":
					$this->cFromName = $mValue;
					break;

				//is there a return address
				case "return":
				case "returnPath":
				case "returnAddress":
					$this->cReturn = $mValue;
					break;

				//Boundary e.g. ---Hammer---
				case "boundry":
				case "boundary":
					$this->setBoundry($mValue);
					break;
			}
		}

		//now actually create teh message
		$this->createHeaders();
		$this->createMessage();


		//see if -f should be used or not
		$bLogin = false;
		if (defined("emailed")) {
			if (strstr($this->cFrom, emailed)) {
				$bLogin = true;
			}
		}

		//actually send the message
		if ($bLogin) {
			$cLogin = "-f " . $this->cFrom;
			return mail($this->cTo, $this->cSubject, $this->cBody, $this->cHeaders, $cLogin);
		} else {
			return mail($this->cTo, $this->cSubject, $this->cBody, $this->cHeaders);
		}
	}

	/**
	 * Email_Send::createMessage()
	 *
	 * @return string
	 */
	private function createMessage() {
		if ($this->cHTML || $this->cTemplate) {
			$this->cBody = "";

			//Text
			if ($this->cText) {
				$this->cBody .= "--" . $this->cBoundry . "\r\n";
				$this->cBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
				$this->cBody .= "Content-Transfer-Encoding: 8bit\r\n";
				$this->cBody .= $this->cText;
				$this->cBody .= "\r\n";
			}

			//HTML part
			if ($this->cHTML) {
				$this->cBody .= "--" . $this->cBoundry . "\r\n";
				$this->cBody .= "Content-Type: text/html; charset=UTF-8\r\n";
				$this->cBody .= "Content-Transfer-Encoding: 8bit\r\n";
				if ($this->cTemplate) {
					$this->cBody .= $this->useTemplate();
				} else {
					$this->cBody .= $this->cHTML;
				}
				$this->cBody .= "\r\n";
			}

			//attachments
			if ($this->cAttachments) {
				$this->cBody .= "--" . $this->cBoundry . "\r\n";
				$this->cBody .= $this->cAttachments;
			}

			$this->cBody .= "--" . $this->cBoundry . "--\r\n";
		} else {
			$this->cBody  = $this->cText;
			$this->cBody .= "\r\n";
		}

		return $this->cBody;
	}

	/**
	 * Email_Send::createHeaders()
	 *
	 * @return string
	 */
	private function createHeaders() {
		//From
		if ($this->cFromName) {
			$this->cHeaders	 = "From: ";
			$this->cHeaders .= $this->cFromName;
			$this->cHeaders .= " <";
			$this->cHeaders .= $this->cFrom;
			$this->cHeaders .= ">\r\n";
		} else {
			$this->cHeaders  = "From: ";
			$this->cHeaders .= $this->cFrom;
			$this->cHeaders .= "\r\n";
		}

		//Agent
		$this->cHeaders .= $this->cAgent;

		//Return
		if ($this->cReturn) {
			if ($this->cFrom) {
				$this->cHeaders .= "Return-Path: " . $this->cReturn . "\r\n";
				$this->cHeaders .= "Return-path: <" . $this->cReturn . ">\r\n";
			}
		} else {
			$this->cHeaders .= "Return-Path: " . $this->cFrom . "\r\n";
			$this->cHeaders .= "Return-path: <" . $this->cFrom . ">\r\n";
		}

		//is there a html content in which case set the content type
		if ($this->cHTML || $this->cTemplate) {
			$this->cHeaders .= "Content-Type: multipart/mixed; boundary=\"" . $this->cBoundry . "\"\r\n";
		} else {
			$this->cHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
		}

		//Last MIME so it works with outlook
		$this->cHeaders .= "MIME-Version: 1.0\r\n";

		return $this->cHeaders;
	}

	/**
	 * Email_Send::setTemplate()
	 *
	 * @param string $cTemplate
	 * @return null
	 */
	public function setTemplate($cTemplate) {
		$this->cTemplate = $cTemplate;
	}

	/**
	 * Email_Send::setTemplateParams()
	 *
	 * @param mixed $mParams
	 * @return null
	 */
	public function setTemplateParams($mParams) {
		$this->mTempParams = $mParams;
	}

	/**
	 * Email_Send::useTemplate()
	 *
	 * @return string
	 */
	private function useTemplate() {
		$cReturn = false;

		//is there a template set
		if ($this->cTemplate) {
			$oHammer 	= Hammer::getHammer();
			$oTemplate	= $oHammer->getTemplate();

			$oTemplate->setTemplate($this->cTemplate);

			//is tehre any template params
			if (isset($this->mTempParams)) {
				if (is_array($this->mTempParams)) {
					foreach ($this-mTempParams as $mParam => $mValue) {
						$oTemplate->setVars($mParam, $mValue);
					}
				} else {
					$oTemplate->setVars("content", $this->mTempParams);
				}
			}

			$cReturn = $oTemplate->renderTemplate();
		}

		$this->cFinalTemplate = $cReturn;
		return $this->cFinalTemplate;
	}

	/**
	 * Email_Send::html()
	 *
	 * @param string $cContent
	 * @return string
	 */
	public function html($cContent = false) {
		if ($cContent) {
			$cBody  = "<html>\r\n";
			$cBody .= "<body style=\"font-family:Verdana, Verdana, Geneva, sans-serif; font-size:12px; color:#666666;\">\r\n";
			$cBody .= $cContent . "\r\n";
			$cBody .= "</body>\r\n";
			$cBody .= "</html>\r\n";

			$this->cHTML = $cBody;
		}
		return $this->cHTML;
	}

	/**
	 * Email_Send::addAttachment()
	 *
	 * @param string $cFile
	 * @return string
	 */
	public function addAttachment($cFile) {
		$cBody .= "Content-Type: " . $this->getContentType($cFile) . "\r\n";
		$cBody .= "Content-Transfer-Encoding: BASE64\r\n";
		$cBody .= "Content-Description: " . basename($cFile) . "\r\n";

		//get the file and its size
		$cContent = file_get_contents($cFile);
		$cContent = base64_encode($cContent);
		$cBody .= "Content-Length: " . strlen($cContent) . "\r\n";
		$cBody .= $cContent . "\r\n";
		$cBody .= "--" . $this->cBoundary . "--\r\n";

		$this->cAttachments = $cBody;
		return $this->cAttachments;
	}

	/**
	 * Email_Send::text()
	 *
	 * @param string $cContent
	 * @return string
	 */
	public function text($cContent = false) {
		if ($cContent) {
			//replace the html with plain text
			$cContent = str_replace("<br />", "\r\n", $cContent);

			//preg replaces
			$aSearch = array(
				'`(<html>)`is',
				'`(</html>)`is',
				'`(<body (.+?)>(.+?))`is',
				'`(</body>)`is',
			);

			$aReplace = array(
				'',
				'',
				'\3',
				'',
			);

			//now do the replace
			$cContent = preg_replace($aSearch, $aReplace, $cContent);

			$this->cText = $cContent;
		}
		return $this->cText;
	}

	/**
	 * Email_Send::getContentType()
	 *
	 * @param string $cName
	 * @return string
	 */
	private function getContentType($cName) {
		$cExt 		= strtolower(substr(strrchr($cName, "."), 1));
		$cReturn	= false;

		switch($cExt) {
			//images
			case "jpg":
			case "jpeg":
				$cReturn = "image/jpg";
				break;

			case "png":
				$cReturn = "image/png";
				break;

			case "gif":
				$cReturn = "image/gif";
				break;

				//xml
			case "xml":
				$cReturn = "text/xml";
				break;

				//text
			case "txt":
			case "php":
				$cReturn = "plain/text";
				break;

				//everything else
			default:
				$cReturn = "application/octet-stream";
				break;
		}

		return $cReturn;
	}

}
