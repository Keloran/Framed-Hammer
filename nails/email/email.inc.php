<?php
/**
 * Email
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2009
 * @version $Id: email.inc.php 501 2010-01-05 10:50:44Z keloran $
 * @access public
 */
class Email implements Nails_Interface {
	//Traits
	use Mailer, Text;

	private $pIMAP		= false;
	private $oMailDB	= false;
	private $oNails		= null;
	private $oUser		= false;
	private $cIMAP		= false;

	private static $oMail;

	//user details
	private $cUser		= false;
	private $cPass		= false;
	private $cName		= false;
	private $iPaged		= false;
	private $bConsole	= false;

	//message details
	private $bIsUTF			= false;
	private $iMessageID		= false;
	private $iMessageIDn	= false;
	private $iStruct		= false;
	private $oStruct		= false;
	private $iCount			= false;
	private $bImages		= false;
	private $bIgnoreImageFilter	= false;
	private $aAttachments	= false;

	//server details
	private $cHost		= false;
	private $iPort		= 143; //default port is 143
	public $bTLS		= false; //TLS off by default (proberlly should be on by default)

	/**
	 * Email::__construct()
	 *
	 */
	public function __construct(Nails $oNails = null, $aDetails = false) {
		if (is_object($oNails)) {
			$this->oNails 	= $oNails;
			$this->oUser	= $oNails->getUser();

			//get the version
			if ($this->oNails->checkVersion("webmail", "1.2") == false) {
				//1.2
				$this->oNails->updateVersion("webmail", "1.2", false, "Added Spam Flag");

				//1.1
				$this->oNails->updateVersion("webmail", "1.1", false, "Fixed parser so it displays full urls, and added pagination");

				//1.0
				$this->oNails->addVersion("webmail", "1.0");
			}

			$iPaged			= $this->oUser->getSetting("pageLimit");
			$this->iPaged	= $iPaged ?: 50;
		}

		//get the email details
		$bDetails = $this->getDetails($aDetails);
		if ($bDetails) {
			//open the connection
			$this->openConnection();
		}
	}

	/**
	 * Email::getInstance()
	 *
	 * @param Nails $oNails
	 * @param array $aDetails
	 * @return object
	 */
	static function getInstance(Nails $oNails, $aDetails = null) {
		if (is_null(self::$oMail)) {
			self::$oMail = new Email($oNails, $aDetails);
		}

		return self::$oMail;
	}

	/**
	 * Email::giveStuff()
	 *
	 * @return array
	 */
	public function debugStuff($bNoBody = false) {
		if ($bNoBody) {
			$aReturn        = array(
                "headers"               => $this->getNewHeaders(),
				"attach"                => $this->getAttachements(),
				"struct"                => imap_fetchstructure($this->pIMAP, $this->iMessageIDn),
				"test"                  => imap_fetchbody($this->pIMAP, $this->iMessageIDn, "2"),
				"uid"                   => $this->getMessageID(),
			);
		} else {
			$aReturn	= array(
				"headers"		=> $this->getNewHeaders(),
				"text"			=> $this->getBodyText(),
				"html"			=> $this->getBodyHTML(),
				"attach"		=> $this->getAttachements(),
				"struct"		=> imap_fetchstructure($this->pIMAP, $this->iMessageIDn),
				"test"			=> imap_fetchbody($this->pIMAP, $this->iMessageIDn, "2"),
				"uid"			=> $this->getMessageID(),
			);
		}

		return $aReturn;
	}

	/**
	 * Email::getDetails()
	 *
	 * @return bool
	 */
	private function getDetails($aDetails = false) {
		if ($aDetails) {
			$this->cUser	= $aDetails['user'];
			$this->cPass	= $aDetails['pass'];
			$this->cName	= $aDetails['name'];
			$this->cHost	= (isset($aDetails['server']) ? $aDetails['server'] : (isset($aDetails['host']) ? $aDetails['host'] : false));
			$this->bTLS		= (isset($aDetails['tls']) ? $aDetails['tls'] : false);

			//its console so must be tls
			if (isset($aDetails['console'])) {
				$this->bConsole	= true;
				$this->bTLS = true;
			}
		} else {
			$this->cUser	= $this->oUser->getSetting("email_username");
			$this->cPass	= $this->oUser->getSetting("email_password");
			$this->cHost	= $this->oUser->getsetting("email_server");
			$this->iPort	= $this->oUser->getSetting("email_port") ?: 143;
			$this->bTLS		= $this->oUser->getSetting("tls") ?: false;
			$this->cName	= $this->oUser->getUsername();
		}

		//is there a username, and a password in order to connect
		if ($this->cUser && $this->cPass) { return true; }

		//well it all failed
		return false;
	}

	/**
	 * Email::openConnection()
	 *
	 * @return pointer
	 */
	private function openConnection() {
		$cTLS	= $this->bTLS ? "/novalidate-cert" : "/novalidate-cert";
		$pIMAP	= false;

		//is there a user, cause otherwise why bother
		if ($this->cUser) {
			try {
				$this->cIMAP	= "{" . $this->cHost . ":" . $this->iPort . $cTLS . "}";
				$pIMAP	= imap_open($this->cIMAP, $this->cUser, $this->cPass);
			} catch (Exception $e) {
				throw new Spanner("EMail Server Down", 200);
			}
		}

		//finally got one
		if ($pIMAP) { $this->pIMAP = $pIMAP; }
		return $this->pIMAP;
	}

	/**
	 * Email::closeConnection()
	 *
	 * @return bool
	 */
	private function closeConnection() {
		if ($this->pIMAP) {
			imap_close($this->pIMAP);
			$this->oIMAP	= false;
		}

		return false;
	}

	/**
	 * Email::checkConnection()
	 *
	 * @return bool
	 */
	private function checkConnection() {
		if ($this->pIMAP) { return true; }

		return false;
	}

	/**
	 * Email::getCount()
	 *
	 * @return
	 */
	private function getCount($aHeaders = null) {
		if (!$aHeaders) { $aHeaders	= imap_headers($this->pIMAP); }

		if ($this->checkConnection()) {
			$iHeaders	= count($aHeaders) + 1;
			$this->iCount	= $iHeaders;
		} else {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		return $this->iCount;
	}

	/**
	 * Email::getHeaders()
	 *
	 * @param bool $bClean This returns clean headers [default off]
	 * @param int $iPage This sets teh page, so if you have over 200 messages then you can set a limit of 50 and paginate them
	 * @return array
	 */
	public function getHeaders($bClean = false, $iPage = false) {
		$aReturn	= false;
		$aPaged		= false;

		if ($this->checkConnection()) {
			$aHeaders 		= imap_headers($this->pIMAP);
			$iHeaders		= $this->getCount($aHeaders);

			//Get the clean version
			if ($bClean) {
				//Cleaner titles
				$j	= 0;

				for ($i = 1; $i < $iHeaders; $i++) {
					$cReturn		= $this->getSubject($i);
					$aPaged[$j]['title']	= $cReturn;
					$aPaged[$j]['address']	= urlencode(trim($cReturn));
					$j++;
				}
			} else {
				$aPaged = $aHeaders;
			}

			//get the page
			if ($iPage) {
				$iPage = $iPage + 1;

				if ($iHeaders <= ($this->iPaged * $iPage)) {
					$aReturn = $aPaged;
				} else {
					$iNew 	= ($this->iPaged * ($iPage - 1));
					$iLess	= ($this->iPaged * $iPage);

					$j = 0;
					for ($i = $iNew; $i < $iLess; $i++) {
						if ($bClean) {
							$aReturn[$j]['title']	= $this->getSubject($i);
							$aReturn[$j]['address']	= urlencode(trim($this->getSubject($i)));
							//$aReturn[] = $aHeaders[$i];

							$j++;
						} else {
							$aReturn[] = $aHeaders[$j];
						}
					}
				}
			} else {
				$aReturn = $aPaged;
			}
		}

		return $aReturn;
	}

	/**
	 * Email::getMessageID()
	 *
	 * @param int $iMessage
	 * @return int
	 */
	public function getMessageID($iMessageID = false) {
		$iReturn	= false;

		if ($iMessageID === false) { $iMessageID = $this->iMessageIDn; }

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$iReturn = imap_uid($this->pIMAP, $iMessageID);
		}

		return $iReturn;
	}

	/**
	 * Email::getMessageNumber()
	 *
	 * @param int $iMessageID
	 * @return int
	 */
	private function getMessageNumber($iMessageID) {
		$iReturn	= false;

		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$iReturn = imap_msgno($this->pIMAP, $iMessageID);
		}

		return $iReturn;
	}

	/**
	 * Email::getMessage()
	 *
	 * @param int $iMessageID
	 * @return array
	 */
	public function getMessage($iMessageID, $bText = null, $bImages = null) {
		$aReturn	= false;

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		//if the number is above 1001 most likelly ive been passed a uid, so need the msgno
		$iMessageNo = $this->getMessageNumber($iMessageID);
		if ($iMessageNo) { $iMessageID = $iMessageNo; }

		//now we have a valid number, get the details
		if ($this->checkConnection()) {
			$this->setMessageID($iMessageID);
			//$aReturn		= $this->getMessageDetails($iMessageID, $bText, $bImages);

			$aReturn['newheaders']		= $this->getNewHeaders();
			$aReturn['newtext']			= $this->getBodyText();
			$aReturn['newhtml']			= $this->getBodyHTML();
			$aReturn['newattachments']	= $this->getAttachements();
			$aReturn['messageid']		= $iMessageID;
			$aReturn['messageno']		= $iMessageNo;
			$aReturn['messageuid']		= $this->getMessageID();
		}

		return $aReturn;
	}

	/**
	 * Email::doMessageNo()
	 *
	 * @param int $iMessageID
	 * @return array
	 */
	public function doMessageNo($iMessageID) {
		//Set the message to 1 because 0 is invalid
        if ($iMessageID == 0) { $iMessageID = 1; }

		//if the number is above 1001 most likelly ive been passed a uid, so need the msgno
        $iMessageNo = $this->getMessageNumber($iMessageID);
        if ($iMessageNo) { $iMessageID = $iMessageNo; }

        //now we have a valid number, get the details
        if ($this->checkConnection()) {
                $this->setMessageID($iMessageID);
		}

		$aMessageNo['number']	= $iMessageNo;
		$aMessageNo['uid']	= $iMessageID;

		return $aMessageNo;
	}

	/**
	 * Email::getAttachements()
	 *
	 * @return array
	 */
	public function getAttachements() {
		$oAttach 	= new Email_Attachments($this->pIMAP, $this->iMessageIDn);
		$mReturn	= $oAttach->getAttachments();

		return $mReturn;
	}

	/**
	 * Email::getAttachmentsObject()
	 *
	 * @return object
	 */
	public function getAttachmentsObject() {
		$oAttach	= new Email_Attachments($this->pIMAP, $this->iMessageIDn);

		return $oAttach;
	}

	/**
	 * Email::getAttachment()
	 *
	 * @param int $iPart
	 * @param string $cType
	 * @param string $cFilename
	 * @param int $iLength
	 * @return null
	 */
	public function getAttachment($iPart, $cType, $cFilename, $iLength, $iEnc) {
		$oAttach	= new Email_Attachments($this->pIMAP, $this->iMessageIDn);
		$mReturn	= $oAttach->getAttachment($iPart, $cType, $cFilename, $iLength, $iEnc);

		echo $mReturn;
	}

	/**
	 * Email::saveAttachment()
	 *
	 * @param int $iPart
	 * @param string $cFilename
	 * @param int $iEnc
	 * @return string
	 */
	public function saveAttachment($iPart, $cFilename, $iEnc) {
		$oAttach	= new Email_Attachments($this->pIMAP, $this->iMessageIDn);
		$mReturn	= $oAttach->saveAttachment($iPart, $cFilename, $iEnc);

		return $mReturn;
	}

	/**
	 * Email::setMessageID()
	 *
	 * @param int $iMessageID
	 * @return null
	 */
	private function setMessageID($iMessageID) {
		$this->iMessageIDn = $iMessageID;
	}

	/**
	 * Email::getPages()
	 *
	 * @return
	 */
	public function getPages() {
		$oHeaders	= new Email_Headers($this->pIMAP);
		return	$oHeaders->getPages();
	}

	/**
	 * Email::getNewHeaders()
	 *
	 * @return array
	 */
	public function getNewHeaders() {
		$oHeaders	= new Email_Headers($this->pIMAP, $this->iMessageIDn);
		$aReturn	= false;

		//from
		$aReturn['from']		= $oHeaders->getHeader('fromaddress');
		$aReturn['fromFull']	= $oHeaders->getHeader('from');
		$aReturn['fromNice']	= $oHeaders->makeNice($aReturn['fromFull']);

		//to
		$aReturn['to']			= $oHeaders->getHeader('toaddress');
		$aReturn['toFull']		= $oHeaders->getHeader('to');
		$aReturn['toNice']		= $oHeaders->makeNice($aReturn['toFull']);

		//reply
		$aReturn['reply']	= $oHeaders->getHeader('reply_toaddress');
		$aReturn['replyFull']	= $oHeaders->getHeader('reply_to');
		$aReturn['replyNice']	= $oHeaders->makeNice($aReturn['replyFull']);

		//sender (possible forge protection)
		$aReturn['sender']		= $oHeaders->getHeader('senderaddress');
		$aReturn['senderFull']	= $oHeaders->getHeader('sender');
		$aReturn['senderNice']	= $oHeaders->makeNice($aReturn['senderFull']);

		//other stuffs
		$aReturn['dated']	= $oHeaders->getHeader('MailDate');
		$aReturn['size']	= $oHeaders->getHeader('size');
		$aReturn['unixdate']	= $oHeaders->getHeader('udate');

		//Subject
		$aReturn['subject']	= $oHeaders->cleanHeader($oHeaders->getHeader('subject'));
		if (!$aReturn['subject']) { $aReturn['subject'] = "** No Subject **"; }
		$aReturn['title']	= $aReturn['subject'];

		//debug
		$aReturn['debug']	= $oHeaders->getHeaders();

		return $aReturn;
	}

	/**
	 * Email::getBoxHeaders()
	 *
	 * @return array
	 */
	public function getBoxHeaders($iLimit = false, $iPage = false) {
		$oHeaders	= new Email_Headers($this->pIMAP);
		return $oHeaders->getAllHeaders($iLimit, $iPage);
	}

	/**
	 * Email::getBodyText()
	 *
	 * @return string
	 */
	public function getBodyText() {
		$oText		= new Email_Text($this->pIMAP, $this->iMessageIDn);
		$cReturn	= $oText->getBody();

		return $cReturn;
	}

	/**
	 * Email::getBodyHTML()
	 *
	 * @return string
	 */
	public function getBodyHTML() {
		$oHTML		= new Email_HTML($this->pIMAP, $this->iMessageIDn);
		$cReturn	= $oHTML->getBody();

		return $cReturn;
	}

	/**
	 * Email::getGID()
	 *
	 * @param int $iMessageID
	 * @return int
	 */
	public function getGID($iMessageID) {
		$iReturn	= false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if ($this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$iReturn = imap_uid($this->pIMAP, $iMessageID);
		}

		return $iReturn;
	}

	/**
	 * Email::deleteMessage()
	 *
	 * @desc this sets the flag to be deleted, it isnt actually deleted till you call clearMailbox()
	 * @param int $iMessageID
	 * @return bool
	 */
	public function deleteMessage($iMessageID) {
		$bReturn	= false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$bReturn	= imap_delete($this->pIMAP, $iMessageID);
		}

		return $bReturn;
	}

	/**
	 * Email::unDeleteMessage()
	 *
	 * @param int $iMessageID
	 * @return
	 */
	public function unDeleteMessage($iMessageID) {
		$bReturn	= false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$bReturn	= imap_undelete($this->pIMAP, $iMessageID);
		}

		return $bReturn;
	}

	/**
	 * Email::setFlag()
	 *
	 * @param int $iMessageID
	 * @param string $cFlag
	 * @return
	 */
	public function setFlag($iMessageID, $cFlag) {
		$bReturn	= false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if ($this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			switch ($cFlag) {
				case "delete":
					$bReturn = imap_setflag_full($this->pIMAP, $iMessageID, "\Deleted");
					break;

				case "undelete":
					$bReturn = imap_clearflag_full($this->pIMAP, $iMessageID, "\Deleted");
					break;

				case "read":
					$bReturn = imap_setflag_full($this->pIMAP, $iMessageID, "\Seen");
					break;

				case "unread":
					$bReturn = imap_clearflag_full($this->pIMAP, $iMessageID, "\Seen");
					break;

				case "spam":
					$cMove		= $this->getSpamFolder();
					$bReturn	= imap_mail_move($this->pIMAP, $iMessageID, $cMove);
					break;
			}
		}

		return $bReturn;
	}

	/**
	 * Email::getSpamFolder()
	 *
	 * @return
	 */
	private function getSpamFolder() {
		$cReturn	= false;

		if ($this->checkConnection()) {
			$oFolders	= imap_getmailboxes($this->pIMAP, $this->cIMAP, "*");
			foreach ($oFolders as $oFolder){
				$iStart		= stripos($oFolder->name, "}") + 1;
				$cFolder	= substr($oFolder->name, $iStart);

				//Yes I know im using the switch wrong since they all do
				//the same thing, this is on purpose to make it human readable
				switch($cFolder){
					case "SPAM":
					case "Spam":
					case "spam":
						$cReturn	= $cFolder;
						break;

					case "Deleted Messages":
					case "Deleted":
					case "Trash":
						$cReturn	= $cFolder;
						break;

					case "Junk":
					case "junk":
					case "JUNK":
						$cReturn	= $cFolder;
						break;

					default:
						$cReturn	= $cReturn;
						break;
				}
			}
		}

		return $cReturn;
	}

	/**
	 * Email::clearMailbox()
	 *
	 * @desc this expunges/purges the mailbox of messages that have the delete flag set
	 * @return bool
	 */
	public function clearMailbox() {
		$bReturn	= false;

		if ($this->checkConnection()) {
			$bReturn	= imap_expunge($this->pIMAP);
		}

		return $bReturn;
	}

	/**
	 * Email::getMessageHeader()
	 *
	 * @param int $iMessageID
	 * @return string
	 */
	private function getMessageHeader($iMessageID) {
		$cReturn	= false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$cReturn = imap_fetchheader($this->pIMAP, $iMessageID);
		}

		return $cReturn;
	}

	/**
	 * Email::getSender()
	 *
	 * @param int $iMessageID
	 * @return string
	 */
	public function getSender($iMessageID) {
		$cReturn	= false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$cHeader	= $this->getMessageBody($iMessageID, "0");
			$cReturn	= $this->getHeaderDetails($cHeader, "From", true);

			if (strstr($cReturn, "<")) {
				$cReturn	= $this->stribet($cReturn, "<", ">");
			}

			if (strstr($cReturn, "(")) {
				$iStart		= strpos($cReturn, "(");
				$cReturn	= substr($cReturn, 0, $iStart);
			}
		}

		return $cReturn;
	}

	/**
	 * Email::getReply()
	 *
	 * @param int $iMessageID
	 * @return string
	 */
	public function getReply($iMessageID) {
		$cReturn	= false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$cHeader	= $this->getMessageBody($iMessageID, "0");
			$cReturn	= $this->getHeaderDetails($cHeader, "Reply-To", true);
		}

		return $cReturn;
	}

	/**
	 * Email::getSubject()
	 *
	 * @param int $iMessageID
	 * @return string
	 */
	public function getSubject($iMessageID, $bSkip = false) {
		$cReturn	 = false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$cHeader	= $this->decodeUTF($this->getMessageBody($iMessageID, "0"));
			$cReturn	= $this->getHeaderDetails($cHeader, "Subject", true);
			$cReturn	= $this->decodeUTF($cReturn);
		}

		return $cReturn;
	}

	/**
	 * Email::getHeaderDetails()
	 *
	 * @param string $cHeader
	 * @param string $cType actually an int, needs to be encased in quotes
	 * @param bool $bValueOnly
	 * @return string
	 */
	private function getHeaderDetails($cHeader, $cType, $bValueOnly = false) {
		$cReturn	= false;

		$cPattern	= '`(' . $cType . ':)(.*)`is';
		$aRows		= explode("\n", $cHeader);
		$iRows		= count($aRows);
		for ($i = 0; $i < $iRows; $i++) {
			if (strstr($aRows[$i], $cType)) {
				preg_match_all($cPattern, $aRows[$i], $aMatches);

				if (isset($aMatches[2][0])) {
					$cReturn	= $aMatches[2][0];
				} else if (isset($aMatches[2])) {
					$cReturn	= $aMatches[2];
				} else {
					$cReturn	= $aMatches;
				}
			}
		}

		if ($cType == "Subject") {
			if (strlen($cReturn) == 2) { $cReturn = "No Subject"; }
		}

		return $cReturn;
	}

	/**
	 * Email::decodeUTF()
	 *
	 * @param string $cContent
	 * @return
	 */
	private function decodeUTF($cContent) {
		$cReturn	= false;
		$iType		= $this->iStruct;

		switch ($iType) {
			case 3:
				$cContent	= imap_base64($cContent);
				break;

			case 4:
				$cContent	= imap_qprint($cContent);
				$cContent	= urldecode($cContent);
				$cContent	= utf8_encode($cContent);
				break;

			case 2:
				$cContent	= imap_binary($cContent);
				break;

			default:
				$aReturn	= imap_mime_header_decode($cContent);
				$iReturn	= count($aReturn);

				if ($iReturn >= 2) {
					for ($i = 0; $i < $iReturn; $i++) {
						$cContent	.= $aReturn[$i]->text;
					}
				} else {
					if (isset($aReturn[0])) {
						$cContent = $aReturn[0]->text;
					}
				}
				break;
		}

		$cReturn = $cContent;
		$cReturn = str_replace('3D"', '"', $cReturn);
		$cReturn = str_replace("3DISO", "ISO", $cReturn);

		return $cReturn;
	}

	/**
	 * Email::showHeaders()
	 *
	 * @param int $iMessageID
	 * @return null
	 */
	public function showHeaders($iMessageID) {
		printRead($this->iStruct);
		printRead($this->getFullDetails($iMessageID));
	}

	/**
	 * Email::getStructure()
	 *
	 * @param int $iMessageID
	 * @return int
	 */
	private function getStructure($iMessageID) {
		$iReturn	= false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$oStruct		= imap_fetchstructure($this->pIMAP, $iMessageID);
			$this->oStruct	= $oStruct;

			if (isset($oStruct->parts[0]->encoding)) {
				$iReturn	= $oStruct->parts[0]->encoding;
			} else {
				$iReturn	= $oStruct->encoding;
			}
		}

		return $iReturn;
	}

	/**
	 * Email::getMessageBody()
	 *
	 * @param int $iMessageID
	 * @param string $cPart This is really an int but has to be encased in quotes
	 * @return mixed
	 */
	private function getMessageBody($iMessageID, $cPart) {
		$mReturn	= false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail/");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			if ($cPart == 1) {
				if (isset($this->oStruct->parts[1]->parts)) {
					$iParts		= count($this->oStruct->parts[1]->parts);
					$mReturn	= imap_fetchbody($this->pIMAP, $iMessageID, $cPart);

					for ($i = 0; $i < $iParts; $i++) {
						$cParts = "1." . $i;

						$mReturn .= imap_fetchbody($this->pIMAP, $iMessageID, $cParts);
					}
				} else {
					$mReturn = imap_fetchbody($this->pIMAP, $iMessageID, $cPart);
				}
			} else {
				$mReturn = imap_fetchbody($this->pIMAP, $iMessageID, $cPart);
			}
		}

		return $mReturn;
	}

	/**
	 * Email::getMessageContent()
	 *
	 * @param int $iMessageID
	 * @return string
	 */
	private function getMessageContent($iMessageID) {
		$mReturn	= false;
		$bAll		= false;
		$cBody		= false;
		$mReturn_a	= false;

		//gone to far
		if ($iMessageID > $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail/");
			}
		}

		//message 0 invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		//are we still connected
		if ($this->checkConnection()) {
			$oStruct_a	= imap_bodystruct($this->pIMAP, $iMessageID, 1);
			$oStruct_b	= imap_bodystruct($this->pIMAP, $iMessageID, 2);

			if ($this->oNails->cChoice == "query") {
				printRead($oStruct_a);
				printRead($oStruct_b);
				printRead(imap_fetchstructure($this->pIMAP, $iMessageID));
				printRead(imap_headerinfo($this->pIMAP, $iMessageID));
			}

			//prefer to get the html version
			if (is_object($oStruct_b)) {
				$cBody	= imap_fetchbody($this->pIMAP, $iMessageID, 2);

				if ($oStruct_b->subtype == "HTML") {
					$mReturn	= $this->returnBody($cBody, $oStruct2->encoding);
					$bAll		= true;
				} else if ($oStruct_b->subtype == "PNG") {
					$mReturn = $this->returnImage($oStruct2, $iMessageID, 2);
					$bAll		= true;
				} else {
					if (is_array($oStruct_b->parameters)) {
						if (strtolower($oStruct_b->parameters[0]->value) == "utf-8") {
							$mReturn_a = utf8_decode($cBody);
						} else {
							$mReturn_a = $cBody;
						}
					} else {
						$mReturn_a	= $cBody;
					}
				}
			}

			if (!$bAll) {
				if (is_object($oStruct_a)) { //if no html version at all
					$cBody		= imap_fetchbody($this->pIMAP, $iMessageID, 1);
					$aParams	= $oStruct_a->parameters[0];

					if ($oStruct_a->subtype == "PLAIN") {
						if ($aParams->value == "UTF-8") {
							$mReturn = utf8_decode($cBody);
						} else {
							$mReturn = $cBody;
						}

						$mReturn = $this->parseMail($mReturn);
					} else if ($oStruct_a->subtype == "JPEG") {
						$mReturn = $this->returnImage($oStruct_a, $iMessageID, 1);
					} else if ($oStruct_a->subtype == "PNG") {
						$mReturn = $this->returnImage($oStruct_a, $iMessageID, 1);
					} else if ($oStruct_a->subtype == "TIFF") {
						$mReturn = "Image invalid";
					} else {
						$mReturn = $this->returnBody($cBody, $oStruct_a->encoding);

						//now take the parameters and do something with them
						if (strtolower($aParams->value) == "utf-8") {
							$mReturn = utf8_decode($mReturn);
						}
					}
				}
			}
		}

		return $mReturn . $mReturn_a;
	}

	/**
	 * Email::returnBody()
	 *
	 * @param string $cBody
	 * @param int $iEncoding
	 * @return string
	 */
	private function returnBody($cBody, $iEncoding) {
		$cReturn	= false;

		switch ($iEncoding) {
			/*case 1:
				$cReturn = imap_utf7_decode($cBody);
				break;*/

			case 2:
				$cReturn = utf8_decode($cBody);
				break;

			case 3:
				$cReturn = base64_decode($cBody);
				break;

			case 4:
				$cReturn = quoted_printable_decode($cBody);
				break;

			default:
				$cReturn = $cBody;
				break;
		}

		return $cReturn;
	}

	/**
	 * Email::returnImage()
	 *
	 * @param object $oStruct
	 * @param int $iMessageID
	 * @param int $iMain
	 * @param int $iPart
	 * @return mixed
	 */
	private function returnImage($oStruct, $iMessageID, $iMain = 1, $iPart = 0) {
		$mReturn = false;

		if (isset($oStruct->dparameters[0])) {
			$iCount = count($oStruct->dparameters);

			if ($iCount >= 2 && ($iCount != $iPart)) {
				$mReturn .= $this->returnImage($oStruct, $iMain, $iPart+1);
			} else {
				$cName = $oStruct->dparameters[$iPart]->value;

				if ($iPart == 0) {
					$fPart = $iMain;
				} else {
					$fPart = $iMain . "." . $iPart;
				}

				if ($oStruct->encoding == 3) {
                	       		$cFile = trim(strtolower("/files/" . $iMessageID . $cName));
                        	        $cCont = base64_decode(imap_fetchbody($this->pIMAP, $iMessageID, $fPart));

					if (!file_exists(SITEPATH . $cFile)) {
	                                	file_put_contents(SITEPATH . $cFile, $cCont);
					}

					$a = "<img src=\"" . $cFile . "\" />";
					$mReturn = $a;
					$this->bIgnoreImageFilter = true;
                        	}
                	}
		}

		return $mReturn;
	}

	/**
	 * Email::getMessageDetails()
	 *
	 * @param int $iMessageID
	 * @return array
	 */
	private function getMessageDetails($iMessageID, $bText = null, $bImages = null) {
		$aReturn	= false;
		$cBody		= false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID >= $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}


		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$cHeader				= $this->decodeUTF($this->getMessageBody($iMessageID, "0"));
			$iStruct				= $this->getStructure($iMessageID);

			$this->iMessageID		= $iMessageID;
			$this->iStruct			= $iStruct;

			$aReturn['id']			= $iMessageID;

			$aReturn['title']		= $this->getHeaderDetails($cHeader, "Subject");
			$aReturn['recieved']	= $this->getHeaderDetails($cHeader, "Date");
			$aReturn['sender']		= $this->getHeaderDetails($cHeader, "From");
			$aReturn['reply']		= $this->getHeaderDetails($cHeader, "Reply-To");

			//body this will need fixing with other features later
			if (!$bText) {
				$cBody	= $this->getMessageContent($iMessageID);

				if ($this->bIgnoreImageFilter) { $bImages = true; }
				$cBody	= $this->stripContainer($cBody, $bImages);
				if ($this->bIgnoreImageFilter) { $bImages = false; }
			}

			if ($cBody) {
				$aReturn['body']	= $cBody;
			} else {
				$aReturn['body']	= $this->parseMail($this->getMessageBody($iMessageID, "1"));
			}

			//Flags
			$aReturn['deleted']	= $this->getFullHeaders($iMessageID, "Deleted");
			$aReturn['read']	= $this->getFullHeaders($iMessageID, "Unseen");
			$aReturn['senderEmail']	= $this->getSender($iMessageID);
			$aReturn['images']	= $this->bImages;
		}

		return $aReturn;
	}

	/**
	 * Email::parseMail()
	 *
	 * @param string $cMessage
	 * @return
	 */
	private function parseMail($cMessage) {
		$cReturn	= $cMessage;
		$cReturn	= $this->decodeUTF($cReturn);
		$cReturn	= str_replace('=\n', "", $cReturn);
		$cReturn	= nl2br($cReturn);

		//make href links href links
		$cReturn	= preg_replace('`((http|https)://)([a-zA-Z0-9\#\-_\&\%\./\?=@:\*]+)`is', '<a href="\1\3" target="_blank">\1\3</a>', $cReturn);

		$cReturn = $this->stripContainer($cReturn);

		return $cReturn;
	}

	/**
	 * Email::stripContainer()
	 *
	 * @param string $cMessage
	 * @return string
	 */
	private function stripContainer($cMessage, $bImages = false) {
		$cReturn	= $cMessage;

		//now strip the body and html tags to make it valid
		$aSearch = array(
			'`(<html>)`is',
			'`(</html>)`is',
			'`(<body(.+?)>)`is',
			'`(<body>)`is',
			'`(</body>)`is',
			'`(<head>)`is',
			'`(</head>)`is',
			'`(<title>(.+?)</title>)`is',
			'`(<base(.+?)/>)`is',
			'`(body{(.+?)})`is',
			'`(<style type="text/css">(.+?)</style>)`is',
		);

		$aReplace 	= array("");
		$cReturn	= preg_replace($aSearch, $aReplace, $cReturn);

		if (strstr($cReturn, "img")) {
			$this->bImages = true;
		}

		if (!$bImages) {
			$aSearch 	= array('`(<img(.+?)/>)`is', '`(</img>)`is', '`(<img(.+?)>)`is');
			$cReturn 	= preg_replace($aSearch, $aReplace, $cReturn);
		}

		return $cReturn;
	}

	/**
	 * Email::getFullHeaders()
	 *
	 * @param int $iMessageID
	 * @param string $cType
	 * @return
	 */
	private function getFullHeaders($iMessageID, $cType) {
		$cReturn	= false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$oHeaders	= imap_headerinfo($this->pIMAP, $iMessageID);

			switch ($cType) {
				case "Unseen":
					$cFlag  = $oHeaders->Recent;
					if ($cFlag == " ") {
						$cFlag = $oHeaders->Unseen;
					}
					break;

				default:
					$cFlag = $oHeaders->$cType;
					break;
			}

			if ($cFlag != " ") {
				$cReturn	= $cFlag;
			}
		}

		return $cReturn;
	}

	/**
	 * Email::getFullDetails()
	 *
	 * @param int $iMessageID
	 * @return array
	 */
	public function getFullDetails($iMessageID) {
		$aReturn	= false;

		//the message requested is higher than the amount in mailbox so kill it
		if ($iMessageID > $this->getCount()) {
			if (!$this->bConsole) {
				$this->oNails->sendLocation("/webmail");
			}
		}

		//Set the message to 1 because 0 is invalid
		if ($iMessageID == 0) { $iMessageID = 1; }

		if ($this->checkConnection()) {
			$aReturn[] = imap_fetchstructure($this->pIMAP, $iMessageID);
			$aReturn[] = imap_fetchbody($this->pIMAP, $iMessageID, "0");
		}

		return $aReturn;
	}

	/**
	 * Email::sendEmail()
	 *
	 * @param string $cTo
	 * @param string $cSubject
	 * @param string $cContent
	 * @return bool
	 */
	public function sendEmail($cTo, $cSubject, $cContent, $cContentText) {
		return $this->sendMail($cTo, $cSubject, $cContent, $cContentText, $this->cUser, $this->cName, $this->cUser);
	}

	/**
	 * Email::addAttachment()
	 *
	 * @param string $cName
	 * @return null
	 */
	public function addAttachment($cName) {
		$this->aAttachments[]['file'] = $cName;
	}

	/**
	* Email::getLastMessage()
	*
	* @desc this is to find out what the number of the last email is, so that pagination works
	* @return int
	*/
	public function getLastMessage() {
		$iReturn	= false;

		if ($this->checkConnection()) {
			$aHeaders	= $this->getHeaders();
			$iReturn	= count($aHeaders);
		}

		return $iReturn;
	}

	/**
	 * Email::__destruct()
	 *
	 */
	public function __destruct() {
		$this->closeConnection();
	}

	/**
	 * Email::getContenType()
	 *
	 * @param string $cName
	 * @return string
	 */
	public function getContentType($cName) {
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
