<?php
/**
 * Messages
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: messages.inc.php 63 2009-09-22 09:06:11Z keloran $
 * @access public
 */
class Messages implements Nails_Interface {
    private $oUser		= false;
	private $oSession	= false;
	private $oDB		= false;
	private $oNails		= false;

    public $iUserID     = false;
    public $iGroupID    = false;
    public $iLimit      = false;
    public $aConfig     = false;

    static $oMessages;

    /**
     * Messages::__construct()
     *
     */
    private function __construct(Nails $oNails) {
        $this->oUser	= $oNails->getUser();
        $this->oSession	= $oNails->getSession();
    	$this->oDB		= $oNails->getDatabase();
		$this->oNails	= $oNails;

        $this->iUserID	= $this->oUser->getUserID();
        $this->iGroupID = $this->oUser->getUserGroupID();
        $this->iLimit   = $this->oUser->getUserLimit();
    }

    /**
     * Messages::getInstance()
     *
     * @param Nails $oNails
     * @return object
     */
    static function getInstance(Nails $oNails) {
    	if (!isset(self::$oMessages)) {
    		self::$oMessages = new Messages($oNails);
    	}

    	return self::$oMessages;
    }

    /**
     * Messages::getMessages()
     *
     * @param bool $bAll
     * @return mixed
     */
    public function getMessages($bAll = false) {
        $bRead = $bAll ? "" : " AND bRead = 0 ";
        $iStart = $this->iPage ? $this->iLimit * ($this->iPage - 1) : 0;

    	$aRead = array($this->iUserID, $iStart, $this->iLimit);
        $this->oDB->read("
            SELECT
                users_messages.iMessageID,
                users_messages.cTitle,
                users_messages.bRead,
                FROM_UNIXTIME(users_messages.tsDate, '%d/%m/%Y %H:%s') as dated,
                users.cUsername,
                users.iUserID
            FROM users_messages
            LEFT JOIN users ON users.iUserID = users_messages.iSenderID
            WHERE iRecieverID = ? " .  $bRead . "
            ORDER BY iMessageID DESC
            LIMIT ?, ?", $aRead);
        $i = 0;
        while($this->oDB->nextRecord()) {
            $aReturn[$i]['title']       = $this->oDB->f('cTitle');
            $aReturn[$i]['id']          = $this->oDB->f('iMessageID');
            $aReturn[$i]['sender']      = $this->oDB->f('cUsername');
            $aReturn[$i]['senderid']    = $this->oDB->f('iUserID');
            $aReturn[$i]['dated']       = $this->oDB->f('dated');

            if ($this->oDB->f('bRead') == 1) {
                $aReturn[$i]['status'] = "read";
            } else {
                $aReturn[$i]['status'] = "unread";
            }

            $i++;
        }

        if (isset($aReturn)) {
            return $aReturn;
        } else {
            return false;
        }
    }

    /**
     * Messages::readMessage()
     *
     * @return mixed
     */
    public function readMessage() {
    	$aRead = array($this->iItem, $this->iUserID);
        $this->oDB->read("
            SELECT
                users_messages.cTitle,
                users_messages.cMessage,
                FROM_UNIXTIME(users_messages.tsDate, '%d/%m/%y %H:%s') as dated,
                users.cUsername,
                users.iUserID
            FROM users_messages
            LEFT JOIN users ON users.iUserID = users_messages.iSenderID
            WHERE iMessageID = ?
            AND iRecieverID = ?
            LIMIT 1", $aRead);
        while($this->oDB->nextRecord()) {
            $aReturn['title']       = $this->oDB->f('cTitle');
            $aReturn['message']     = $this->oDB->f('cMessage');
            $aReturn['dated']       = $this->oDB->f('dated');
            $aReturn['sender']      = $this->oDB->f('cUsername');
            $aReturn['senderid']    = $this->oDB->f('iUserID');
        }

        if (isset($aReturn)) {
        	$this->changeStatus(1);
            return $aReturn;
        } else {
            return false;
        }
    }

    /**
     * Messages::sendMessage()
     *
     * @param string $cTitle
     * @param string $cMessage
     * @param int $iReciever
     * @return bool
     */
    public function sendMessage($cTitle, $cMessage, $iReciever) {
        $aEscape = array($cTitle, $cMessage, $this->iUserID, $iReciever);
        $this->oDB->write("INSERT INTO user_messages (cTitle, cMessage, tsDate, iSender, iReciever) VALUES (?, ?, UNIX_TIMESTAMP, ?, ?)", $aEscape);
        if (!$this->oDB->cError) {
        	$this->oSession->addAction("Sent a Message -- " . $this->oDB->insertID());
            return true;
        }
    }

    /**
     * Messages::deleteMessage()
     *
     * @return bool
     */
    public function deleteMessage() {
    	$aRead = array($this->iItem, $this->iUserID);
        $this->oDB->read("SELECT iMessageID FROM users_messages WHERE iMessageID = ? AND iRecieverID = ? LIMIT 1", $aRead);
        if ($this->oDB->nextRecord()) {
            $this->oDB->write("DELETE FROM users_messages WHERE iMessageID = ? LIMIT 1", $this->iItem);

			$this->oSession->addAction("Deleted a Message -- " . $this->iItem);
            if (!$this->cError) { return true; }
        } else {
            return false;
        }
    }

    /**
     * Messages::changeStatus()
     *
     * @param bool $bStatus
     * @return bool
     */
    public function changeStatus($bStatus) {
    	$aRead = array($this->iItem, $this->iUserID);
        $this->oDB->read("SELECT iMessageID FROM users_messages WHERE iMessageID = ? AND iRecieverID = ? LIMIT 1", $aRead);
        if ($this->oDB->nextRecord()) {
        	$aWrite = array($bStatus, $this->iItem);
            $this->oDB->write("UPDATE users_messages SET bRead = ? WHERE iMessageID = ? LIMIT 1", $aWrite);
            $this->oSession->addAction("Updated message status -- " . $this->iItem);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Messages::findID()
     *
     * @param string $cUsername
     * @return mixed
     */
    public function findID($cUsername) {
        $this->oDB->read("SELECT iUserID FROM users WHERE cUsername LIKE (?) LIMIT 1", $cUsername);
        if ($this->oDB->nextRecord()) {
            return $this->oDB->f('iUserID');
        } else {
            return false;
        }
    }

    /**
     * Messages::getCount()
     *
     * @param string $iUserID
     * @param bool $bAll
     * @return int
     */
    public function getCount($iUserID, $bAll = false) {
        $bRead = $bAll ? "" : " AND bRead = 0";

        $this->oDB->read("SELECT COUNT('iMessageID') as iCount FROM users_messages WHERE iRecieverID = ? " . $bRead . " LIMIT 1", $iUserID);
        if ($this->oDB->nextRecord()) {
            return $this->oDB->f('iCount');
        } else {
            return false;
        }
    }

	/**
	 * Messages::notifyUser()
	 *
	 * @param int $iUserID
	 * @param string $cMessage
	 * @return null
	 */
	public function notifyUser($iUserID, $cMessage) {
		$aInsert = array($iUserID, $cMessage);
		$this->oDB->write("INSERT INTO notifications_users (iUserID, cMessage, tsDated) VALUES (?, ?, NOW())", $aInsert);
	}

	/**
	 * Messages::getUserNotifications()
	 *
	 * @return array
	 */
	public function getUserNotifications() {
		$aReturn	= false;
		$i			= 0;

		if ($this->iUserID) {
			$this->oDB->read("SELECT iNotifyID, cMessage FROM notifications_users WHERE iUserID = ? ORDER BY iNotifyID ASC", $this->iUserID);
			while($this->oDB->nextRecord()){
				$aReturn[$i]['id']	= $this->oDB->f('iNotifyID');
				$aReturn[$i]['message'] = $this->oDB->f('cMessage');
				$i++;
			}
		}

		return $aReturn;
	}

	/**
	 * Messages::deleteUserNotification()
	 *
	 * @param int $iNotify
	 * @return null
	 */
	public function deleteUserNotification($iNotify) {
		$aDelete = array($this->iUserID, $iNotify);

		$this->oDB->write("DELETE FROM notifications_users WHERE iUserID = ? AND iNotifyID = ? LIMIT 1", $aDelete);
	}

	/**
	 * Messages::notifyGroup()
	 *
	 * @param int $iGroupID
	 * @param string $cMessage
	 * @return null
	 */
	public function notifyGroup($cMessage, $cPermission) {
		$aInsert = array($cPermission, $cMessage);
		$this->oDB->write("INSERT INTO notifications_groups (cPermission, cMessage,) VALUES (?, ?)", $aInsert);
	}

	/**
	 * Messages::getGroupNotifications()
	 *
	 * @param string $cPermission
	 * @return array
	 */
	public function getGroupNotifications($cPermission) {
		$aReturn	= false;
		$i			= 0;

		//get the group name, e.g. newsAdmin == News
		$cGroup	= $cPermission;
		$iAdmin	= strpos($cGroup, "Admin");
		$cGroup	= ucfirst(substr($cGroup, 0, $iAdmin));

		$aRead		= array($cPermission, (time() - 10000));
		$this->oDB->read("SELECT cMessage FROM notifications_groups WHERE cPermission = ? AND tsDated >= FROM_UNIXTIME(?)", $aRead);
		while($this->oDB->nextRecord()){
			$aReturn[$cGroup][] = $this->oDB->f('cMessage');
		}

		return $aReturn;
	}
}