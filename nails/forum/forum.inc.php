<?php
/**
 * Forum
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: forum.inc.php 163 2009-10-29 10:10:41Z keloran $
 * @access public
 */
class Forum implements Nails_Interface {
    public $oUser		= false;
    public $oSession	= false;
	public $oNails		= false;

	private $oDB		= false;

    public $iUserID     = false;
    public $iGroupID    = false;
	public $iTopicID	= false;
	public $iForumID	= false;
    public $iLimit      = false;
    public $aConfig     = false;
	public $iPage		= false;

    static private $oForum;

    /**
     * Forum::__construct()
     *
     */
    private function __construct(Nails $oNails) {
    	$this->oNails	= $oNails;

		$this->oUser	= $this->oNails->getUser();
		$this->oSession	= $this->oNails->getSession();
    	$this->oDB		= $this->oNails->getDatabase();

        $this->iUserID	= $this->oUser->getUserID();
        $this->iGroupID = $this->oUser->getUserGroupID();
        $this->iLimit   = $this->oUser->getUserLimit();
		$this->iTopicID = $this->oNails->iItem;
		$this->iForumID = $this->oNails->iItem;
    	$this->iPage	= $this->oNails->iPage;

		//do the install
		if ($this->oNails->checkInstalled("forums") == false) {
			if ($this->oUser->canDoThis("install")) {
				$this->install();
			}
		}

		//do the upgrade
		if ($this->oNails->checkVersion("forums", "1.0") == false) {
			//1.0
			$this->oNails->addVersion("forums", "1.0");
		}
	}

	/**
	 * Forum::getInstance()
	 *
	 * @return
	 */
	static function getInstance(Nails $oNails) {
		if (is_null(self::$oForum)) {
			self::$oForum = new Forum($oNails);
		}

		return self::$oForum;
	}

	/**
	 * Forum::install()
	 *
	 * @return
	 */
	private function install() {
		//Add the forums
		$this->oNails->addTable("CREATE TABLE IF NOT EXISTS `forums` (`iForumID` INT NOT NULL AUTO_INCREMENT, `iParentID` INT NOT NULL DEFAULT 0, `cTitle` VARCHAR(150) NOT NULL, `cDescription` VARCHAR(250) NOT NULL, `iGroupID` INT NOT NULL DEFAULT 1, PRIMARY KEY (`iForumID`)) ENGINE = MYISAM");
		$this->oNails->addIndexs("forums", array("iParentID", "iGroupID"));

		//Add the topics
		$this->oNails->addTable("CREATE TABLE IF NOT EXISTS `forums_topics` (`iTopicID` INT NOT NULL AUTO_INCREMENT, `cTitle` VARCHAR(100) NOT NULL, `iForumID` INT NOT NULL, `iPosterID` INT NOT NULL, `tsDate` INT NOT NULL, `cContent` TEXT, `bSticky` BOOL NOT NULL DEFAULT 0, `iViews` INT NOT NULL DEFAULT 0, `bLocked` BOOL NOT NULL DEFAULT 0, PRIMARY KEY (`iTopicID`)) ENGINE = MYISAM");
		$this->oNails->addIndexs("forums_topics", array("iForumID", "iPosterID", "bSticky", "iViews", "bLocked"));

		//Add the replys
		$this->oNails->addTable("CREATE TABLE IF NOT EXISTS `forums_replys` (`iReplyID` INT NOT NULL AUTO_INCREMENT, `iTopicID` INT NOT NULL, `iForumID` INT NOT NULL, `cContent` TEXT, `tsDate` INT NOT NULL, `iPosterID` INT NOT NULL, PRIMARY KEY (`iReplyID`)) ENGINE = MYISAM");
		$this->oNails->addIndexs("forums_replys", array("iTopicID", "iPosterID", "iForumID"));

		//Modify the groups table
		if ($this->oNails->groupsInstalled()) {
			//Modify the users table
			$this->oDB->write("ALTER TABLE `users` ADD `cSignature` VARCHAR(200) NOT NULL, ADD `iPosts` INT NOT NULL DEFAULT 0");
			$this->oNails->addIndexs("users", "iPosts");

			$aGroups = array("forumsReply", "forumsTopic", "forumsSticky", "forumsDelete", "forumsLock");
			$this->oNails->addGroups($aGroups);

			$aAdminAbilitys = array("forumsDelete", "forumsTopic", "forumsReply", "forumsSticky", "forumsLock");
			$this->oNails->addAbility("admin", $aAdminAbilitys);

			$aUserAbilitys = array("forumsTopic", "forumsReply");
			$this->oNails->addAbility("registered", $aUserAbilitys);
		}

		$this->oNails->addVersion("forums", "1.0");

		$this->oNails->sendLocation("install");
    }

	/**
	 * Forum::getForumID()
	 *
	 * @return int
	 */
	public function getForumID() {
		$iReturn	= false;
		if (!$this->oNails->cChoice) { return $iReturn; } //no idea why you need todo this if you have an number

		$cLike		= "%" . $this->oNails->cChoice . "%";
		$this->oDB->read("SELECT iForumID FROM forums WHERE cTitle LIKE (?) LIMIT 1", $cLike);
		if ($this->oDB->nextRecord()) {
			$iReturn = $this->oDB->f('iForumID');
		}

		$this->iForumID = $iReturn;
		return $iReturn;
	}

    /**
     * Forum::getForums()
     *
     * @return
     */
    public function getForums() {
        $cLimit = $this->iForumID ? "LIMIT 1" : "";

        //Get the parent forums
        $this->oDB->read("
        	SELECT forums.iForumID, forums.cTitle
        	FROM forums WHERE forums.iGroupID <= ?
        	AND forums.iParentID = 0
        	ORDER BY iForumID ASC " . $cLimit, $this->iGroupID);
        $i = 0;
        while ($this->oDB->nextRecord()) {
            $aForums[$i]['top']['title'] = $this->oDB->f('cTitle');
            $aForums[$i]['top']['id'] = $this->oDB->f('iForumID');
            $i++;
        }

		if (isset($aForums)) {
			//Get the Children
			$iForums	= count($aForums);

	        for ($i = 0; $i < $iForums; $i++) {
	            $iParent = $this->iForumID ? $this->iForumID : $aForums[$i]['top']['id'];
	            $j = 0;

                //Get the children
		$aRead = array($iParent, $this->iGroupID);
                $this->oDB->read("
                    SELECT forums.iForumID, forums.cTitle, forums.cDescription
                    FROM forums
                    WHERE forums.iParentID = ?
                    AND forums.iGroupID <= ?
                    ORDER BY forums.iForumID ASC", $aRead);
                while($this->oDB->nextRecord()) {
                    $aForums[$i]['child'][$j]['title']          = $this->oDB->f('cTitle');
                    $aForums[$i]['child'][$j]['description']    = $this->oDB->f('cDescription');
                    $aForums[$i]['child'][$j]['id']             = $this->oDB->f('iForumID');
                    $j++;
                    //$aForums[$i]['child'][$j]['topics'] = $this->oDB->f('iTopics');
                    //$aForums[$i]['child'][$j]['replys'] = $this->oDB->f('iReplys');
				}

                //Get the children details
				if (isset($aForums[$i]['child'])) {
	                $iChildren	= count($aForums[$i]['child']);
        	        for ($j = 0; $j < $iChildren; $j++) {
                	    $iChildID = $aForums[$i]['child'][$j]['id'];

	                    //Get the topic count
        	            $this->oDB->read("SELECT COUNT(forums_topics.iTopicID) as iTopics FROM forums_topics WHERE forums_topics.iForumID = ? LIMIT 1", $iChildID);
                	    while($this->oDB->nextRecord()) { $aForums[$i]['child'][$j]['topics'] = $this->oDB->f('iTopics'); }

	                    //Get the reply count
        	            $this->oDB->read("SELECT COUNT(forums_replys.iReplyID) as iReplys FROM forums_replys WHERE forums_replys.iForumID = ? LIMIT 1", $iChildID);
                	    while($this->oDB->nextRecord()) { $aForums[$i]['child'][$j]['replys'] = $this->oDB->f('iReplys'); }

	                    //Get the latest reply date
        	            $this->oDB->read("SELECT forums_replys.tsDate FROM forums_replys WHERE forums_replys.iForumID = ? ORDER BY iReplyID DESC LIMIT 1", $iChildID);
                	    $tsReplyDate = $this->oDB->nextRecord() ? $this->oDB->f('tsDate') : 0;

	                    //Get the latest topic date
        	            $this->oDB->read("SELECT forums_topics.tsDate FROM forums_topics WHERE forums_topics.iForumID = ? ORDER BY iTopicID DESC LIMIT 1", $iChildID);
                	    $tsTopicDate = $this->oDB->nextRecord() ? $this->oDB->f('tsDate') : 0;

	                    //There is a newer topic than the last reply, so show those details
        	            if ($tsTopicDate > $tsReplyDate) {
                	        $this->oDB->read("
                        	    SELECT
                                	forums_topics.cTitle,
	                                forums_topics.iTopicID,
                	                users.cUsername,
        	                        users.iUserID
                        	    FROM forums_topics
	                            LEFT JOIN users ON users.iUserID = forums_topics.iPosterID
        	                    WHERE forums_topics.iForumID = ?
                	            ORDER BY forums_topics.iTopicID DESC
                        	    LIMIT 1", $iChildID);
	                        while($this->oDB->nextRecord()) {
        	                    $aForums[$i]['child'][$j]['lastReply']  = $this->oDB->f('cTitle');
                	            $aForums[$i]['child'][$j]['iLast']      = $this->oDB->f('iTopicID');
                        	    $aForums[$i]['child'][$j]['cPoster']    = $this->oDB->f('cUsername');
	                            $aForums[$i]['child'][$j]['iPoster']    = $this->oDB->f('iUserID');
        	                }

        	                $aForums[$i]['child'][$j]['dLast']     = date("d/m/Y H:i", $tsTopicDate);
	                        $aForums[$i]['child'][$j]['tsLast']    = $tsTopicDate;
        	            } else {
                	        $this->oDB->read("
                        	    SELECT
                                	forums_topics.cTitle,
	                                forums_topics.iTopicID,
        	                        users.cUsername,
                		        users.iUserID
	                            FROM forums_topics
        	                    LEFT JOIN forums_replys ON forums_replys.iTopicID = forums_topics.iTopicID
                	            LEFT JOIN users ON users.iUserID = forums_replys.iPosterID
                        	    WHERE forums_replys.iForumID = ?
	                            ORDER BY forums_replys.iReplyID DESC
        	                    LIMIT 1", $iChildID);
                	        while($this->oDB->nextRecord()) {
                        	    $aForums[$i]['child'][$j]['lastReply']  = $this->oDB->f('cTitle');
	                            $aForums[$i]['child'][$j]['iLast']      = $this->oDB->f('iTopicID');
        	                    $aForums[$i]['child'][$j]['cPoster']    = $this->oDB->f('cUsername');
                	            $aForums[$i]['child'][$j]['iPoster']    = $this->oDB->f('iUserID');
                        	}

	                        $aForums[$i]['child'][$j]['dLast']      = date("d/m/Y H:i", $tsReplyDate);
        	                $aForums[$i]['child'][$j]['tsLast']     = $tsReplyDate;
                	    }
	                }
				} else {
		    		$aForums[$i]['child'] = array();
				}
			}
			return $aForums;
		} else {
			return false;
		}
    }

    /**
     * Forum::showforum()
     *
     * @return
     */
    public function showforum() {
        $iStart = $this->iPage ? $this->iLimit * ($this->iPage - 1) : 0;

        if ($this->iForumID == false) { return false; }

		$aRead = array($this->oSession->lastLogin(), $this->iForumID, $iStart, $this->iLimit);
        $this->oDB->read("
            SELECT
                forums_topics.iTopicID,
                forums_topics.cTitle,
                forums_topics.bSticky,
				forums_topics.bLocked,
                FROM_UNIXTIME(forums_topics.tsDate, '%d/%m/%Y') as postDate,
                forums_topics.iViews,
                users.cUsername,
                (
                    SELECT COUNT(iReplyID)
                    FROM forums_replys
                    WHERE forums_replys.iTopicID = forums_topics.iTopicID
                    LIMIT 1
                ) as iReplys,
                (
                    SELECT FROM_UNIXTIME(forums_replys.tsDate, '%d/%m/%Y')
                    FROM forums_replys
                    WHERE forums_replys.iTopicID = forums_topics.iTopicID
                    ORDER BY forums_replys.iReplyID DESC
                    LIMIT 1
                ) as tsLast,
                (
                    SELECT users.cUsername
                    FROM users
                    LEFT JOIN forums_replys ON forums_replys.iPosterID = users.iUserID
                    WHERE forums_replys.iTopicID = forums_topics.iTopicID
                    ORDER BY iReplyID DESC
                    LIMIT 1
               ) as cLastUser,
			   (
			   		SELECT forums_replys.iReplyID
					FROM forums_replys
					WHERE forums_replys.iTopicID = forums_topics.iTopicID
					AND tsDate > ?
					ORDER BY iReplyID ASC
					LIMIT 1
				) as newReply
            FROM forums_topics
            LEFT JOIN users ON users.iUserID = forums_topics.iPosterID
			lEFT JOIN forums_replys ON forums_replys.iTopicID = forums_topics.iTopicID
            WHERE forums_topics.iForumID = ?
			GROUP BY forums_topics.iTopicID
            ORDER BY forums_topics.bSticky DESC, forums_replys.iTopicID DESC, forums_topics.iTopicID DESC
            LIMIT ?, ?", $aRead);
        $i = 0;
        while ($this->oDB->nextRecord()) {
            $aTopics[$i]['title']       = $this->oDB->f('cTitle');
            $aTopics[$i]['poster']      = $this->oDB->f('cUsername');
            $aTopics[$i]['id']          = $this->oDB->f('iTopicID');
            $aTopics[$i]['replys']      = $this->oDB->f('iReplys');
            $aTopics[$i]['sticky']      = $this->oDB->f('bSticky');
			$aTopics[$i]['locked']		= $this->oDB->f('bLocked');
            $aTopics[$i]['views']       = $this->oDB->f('iViews');
			$aTopics[$i]['newReply']    = $this->oDB->f('newReply');

            if ($this->oDB->f('tsLast')) {
                $aTopics[$i]['lastPost'] = $this->oDB->f('tsLast');
                $aTopics[$i]['last'] = $this->oDB->f('cLastUser');
            } else {
                $aTopics[$i]['lastPost'] = $this->oDB->f('postDate');
                $aTopics[$i]['last'] = $this->oDB->f('cUsername');
            }

            $i++;
        }

		if (isset($aTopics)) {
			return $aTopics;
		} else {
			return false;
		}
    }

    /**
     * Forum::showTopic()
     *
     * @return
     */
    public function showTopic() {
    	$aReturn = false;

    	if ($this->iTopicID == false) { return false; }

        $this->oDB->read("
            SELECT
                forums_topics.cTitle,
                forums_topics.cContent,
				forums_topics.bSticky,
				forums_topics.bLocked,
				COUNT(forums_replys.iReplyID) as numReplys,
                FROM_UNIXTIME(forums_topics.tsDate, '%d/%m/%Y') as dated,
                forums_topics.iPosterID,
                users.cUsername,
                users.iPosts,
                users.cSignature,
                users.cUserImage,
                FROM_UNIXTIME(users.tsDate, '%d/%m/%Y') as joinDate,
                users_groups.cGroup
            FROM forums_topics
            LEFT JOIN users ON users.iUserID = forums_topics.iPosterID
			LEFT JOIN forums_replys ON forums_replys.iTopicID = forums_topics.iTopicID
            LEFT JOIN users_groups ON users_groups.iGroupID = users.iGroupID
            WHERE forums_topics.iTopicID = ?
			GROUP BY forums_topics.iTopicID
            LIMIT 1", $this->iTopicID);
        if ($this->oDB->nextRecord()) {
            $aReturn['title']       = $this->oDB->f('cTitle');
            $aReturn['content']     = $this->oDB->f('cContent');
            $aReturn['dated']       = $this->oDB->f('dated');
            $aReturn['posterid']    = $this->oDB->f('iPosterID');
            $aReturn['poster']      = $this->oDB->f('cUsername');
			$aReturn['sticky']      = $this->oDB->f('bSticky');
			$aReturn['numReplys']   = $this->oDB->f('numReplys');
			$aReturn['locked']      = $this->oDB->f('bLocked');
            $aReturn['joinDate']    = $this->oDB->f('joinDate');
            $aReturn['iPosts']      = $this->oDB->f('iPosts');
            $aReturn['group']       = $this->oDB->f('cGroup');

            $aReturn['userImage'] = $this->oDB->f('cUserImage') ? "/images/userimage/" . $this->oDB->f('cUserImage') : FALSE;

        }

        return $aReturn;
    }

    /**
     * Forum::showReplys()
     *
     * @return
     */
    public function showReplys() {
    	if ($this->iTopicID == false) { return false; }

        $iStart = $this->iPage ? $this->iLimit * ($this->iPage - 1) : 0;

		$aRead = array($this->iTopicID, $iStart, $this->iLimit);
        $this->oDB->read("
            SELECT
                forums_replys.cContent,
                forums_replys.iPosterID,
				forums_replys.iReplyID,
                FROM_UNIXTIME(forums_replys.tsDate, '%d/%m/%Y') as dated,
                users.cUsername,
				users.cSignature,
                users.iPosts,
                users.cUserImage,
                FROM_UNIXTIME(users.tsDate, '%d/%m/%Y') as joinDate,
                users_groups.cGroup
            FROM forums_replys
            LEFT JOIN users ON forums_replys.iPosterID = users.iUserID
            LEFT JOIN users_groups ON users_groups.iGroupID = users.iGroupID
            WHERE forums_replys.iTopicID = ?
            ORDER BY iReplyID ASC
            LIMIT ?, ?", $aRead);
        $i = 0;
        while ($this->oDB->nextRecord()) {
            $aReturn[$i]['content']     = $this->oDB->f('cContent');
            $aReturn[$i]['poster']      = $this->oDB->f('iPosterID');
            $aReturn[$i]['dated']       = $this->oDB->f('dated');
            $aReturn[$i]['username']    = $this->oDB->f('cUsername');
			$aReturn[$i]['id']          = $this->oDB->f('iReplyID');
            $aReturn[$i]['joindate']    = $this->oDB->f('joinDate');
            $aReturn[$i]['iPosts']      = $this->oDB->f('iPosts');
            $aReturn[$i]['group']       = $this->oDB->f('cGroup');

            $aReturn[$i]['userImage']   = $this->oDB->f('cUserImage') ? "/images/userimage/" . $this->oDB->f('cUserImage') : false;

            $i++;
        }

		if (isset($aReturn)) {
			return $aReturn;
		} else {
			return false;
		}
    }

    /**
     * Forum::getForum()
     *
     * @return
     */
    public function getForum() {
    	if ($this->iTopicID == false) { return false; }

        $this->oDB->read("
			SELECT iForumID
			FROM forums_topics
			WHERE iTopicID = " . $this->iTopicID . "
			LIMIT 1");
        if ($this->oDB->nextRecord()) {
            return $this->oDB->f('iForumID');
        }

        return false;
    }

    /**
     * Forum::addReply()
     *
     * @param string $cReply
     * @return
     */
    public function addReply($cReply) {
        $cReply = nl2br($cReply);
        $iForumID = $this->getForum();

		if ($this->iUserID) {
			$aWrite = array($this->iTopicID, $cReply, $this->iUserID, $iForumID);
	        $this->oDB->write("
			INSERT INTO forums_replys
				(iTopicID, cContent, tsDate, iPosterID, iForumID)
			VALUES
				(?, ?, ?, ?, ?)", $aWrite);

			$this->oSession->addAction("Added Forum Reply -- Topic:" . $this->iTopic . " -- Reply:" . $this->oDB->insertID());
        	$this->oNails->sendLocation("/forums/topic/" . $this->iTopicID . "/?last=true");
		} else {
			$this->oNails->sendLocation("/login");
		}
    }

	/**
	 * Forum::getTopicsCount()
	 *
	 * @return
	 */
	public function getTopicsCount() {
		if ($this->iForumID == false) { return false; }

		$this->oDB->read("SELECT COUNT(forums_topics.iTopicID) as topics FROM forums_topics WHERE iForumID = ? LIMIT 1", $this->iForumID);
		if ($this->oDB->nextRecord()) {
			$iTopics = $this->oDB->f('topics');
		}

		return $iTopics;
	}

	/**
	 * Forum::addTopic()
	 *
	 * @param string $cTitle
	 * @param string $cContent
	 * @param bool $bSticky
	 * @param bool $bLocked
	 * @return
	 */
	public function addTopic($cTitle, $cContent, $bSticky = false, $bLocked = false) {
		$cContent = nl2br($cContent);

		$bSticky = $bSticky ? 1 : 0;
		$bLocked = $bLocked ? 1 : 0;

        if ($this->iUserID) {
        	$aWrite = array($this->iForumID, $cTitle, $cContent, $bSticky, $this->iUserID, $bLocked);
			$this->oDB->write("
				INSERT INTO forums_topics
					(iForumID, cTitle, cContent, bSticky, iPosterID, tsDate, bLocked)
				VALUES
					(?, ?, ?, ?, ?, UNIX_TIMESTAMP(), ?)", $aWrite);
	        $iInsertID = $this->oDB->insertID();

        	$this->oSession->addAction("Added Forum Topic -- " . $iInsertID);
			$this->oNails->sendLocation("/forums/topic/" . $iInsertID);
		} else {
			return false;
		}
	}

	/**
	 * Forum::addView()
	 *
	 * @return
	 */
	public function addView() {
		$this->oDB->write("
			UPDATE forums_topics
			SET iViews = iViews + 1
			WHERE iTopicID = ?
			LIMIT 1", $this->iTopicID);
	}

	/**
	 * Forum::getParent()
	 *
	 * @param int $iType
	 * @return
	 */
	public function getParent($iType) {
	    $aReturn = array();

	    if ($this->iTopicID == false) { return false; }

		if ($iType == 1) { //Topic
			$this->oDB->read("
				SELECT
					forums.iForumID,
					forums.cTitle,
					forums.iParentID as parentID,
					forums_topics.cTitle as topicTitle,
					(
						SELECT forums.cTitle
						FROM forums
						WHERE forums.iForumID = parentID
						LIMIT 1
					) as forumParent
				FROM forums
				LEFT JOIN forums_topics ON forums_topics.iForumID = forums.iForumID
				WHERE forums_topics.iTopicID = ?
				LIMIT 1", $this->iTopicID);
			while ($this->oDB->nextRecord()) {
				$aReturn['title']   = $this->oDB->f('cTitle');
				$aReturn['id']      = $this->oDB->f('iForumID');
				$aReturn['topic']   = $this->oDB->f('topicTitle');

				if ($this->oDB->f('parentID') != 0) {
					$aReturn['parent'] = $this->oDB->f('parentID');
					$aReturn['ptitle'] = $this->oDB->f('forumParent');
				}
			}
		} else if ($iType == 2) { //Forum
			$aRead = array($this->iForumID, $this->iForumID);
			$this->oDB->read("
				SELECT
					forums.cTitle,
					forums.iForumID,
					(
						SELECT forums.cTitle
						FROM forums
						WHERE forums.iForumID = ?
						LIMIT 1
					) as fTitle
				FROM forums
				WHERE iForumID = (
					SELECT forums.iParentID
					FROM forums
					WHERE forums.iForumID = ?
					LIMIT 1)
				LIMIT 1", $aRead);
			while ($this->oDB->nextRecord()) {
				$aReturn['id'] = $this->oDB->f('iForumID');
				$aReturn['title'] = $this->oDB->f('cTitle');
				$aReturn['ftitle'] = $this->oDB->f('fTitle');
			}
		}

		return $aReturn;
	}

	/**
	 * Forum::getLastReply()
	 *
	 * @return
	 */
	public function getLastReply() {
		$aReturn	 = false;

		$this->oDB->read("
			SELECT COUNT(forums_replys.iReplyID) as amount, forums_replys.iReplyID
			FROM forums_replys
			WHERE forums_replys.iTopicID = ?
			GROUP BY forums_replys.iTopicID
			LIMIT 1", $this->iTopicID);
		while ($this->oDB->nextRecord()) {
			$aReturn['page'] = ceil($this->oDB->f('amount') / $this->oUser->getUserLimit());
			$aReturn['id'] = $this->oDB->f('iReplyID');
		}

		if (!$aReturn) {
			$aReturn['page'] = 0;
		}

		return $aReturn;
	}

	/**
	 * Forum::getLastVisit()
	 *
	 * @return
	 */
	public function getLastVisit() {
		if (!function_exists("getCookie")) { include HAMMERPATH . "/functions/cookie.php"; }

		$this->oDB->read("
			SELECT forums_replys.iReplyID
			FROM forums_replys
			WHERE forums_replys.iTopicID = ?", $this->iTopicID);
		$i = 0;
		while ($this->oDB->nextRecord()) {
			$aReplys[$i] = $this->oDB->f('iReplyID');
			$i++;
		}

		$this->oDB->read("
			SELECT COUNT(forums_replys.iReplyID) as total
			FROM forums_replys
			WHERE forums_replys.iTopicID = ?
			GROUP BY forums_replys.iTopicID", $this->iTopicID);
		if ($this->oDB->nextRecord()) {
			$iTotal = $this->oDB->f('total');
		}

		$aRead = array($this->iTopicID, getCookie("lastVisit"));
		$this->oDB->read("
			SELECT forums_replys.iReplyID
			FROM forums_replys
			WHERE forums_replys.iTopicID = ?
			AND UNIX_TIMESTAMP(tsDate) > UNIX_TIMESTAMP(?)
			ORDER BY iReplyID ASC
			LIMIT 1", $aRead);
		if ($this->oDB->nextRecord()) {
			$iReply = $this->oDB->f('iReplyID');
		}

		$iNum = array_search($iReply, $aReplys);

		$aReturn['reply'] = $iReply;
		$aReturn['page'] = ceil($iNum / $this->oUser->getUserLimit());

		return $aReturn;
	}

	/**
	 * Forum::getParents()
	 *
	 * @return array
	 */
	public function getParents() {
		$aReturn	= false;
		$i			= 0;

		$this->oDB->read("SELECT iForumID, cTitle FROM forums WHERE iParentID = 0 ORDER BY iForumID ASC");
		while ($this->oDB->nextRecord()) {
			$aReturn[$i]['id']		= $this->oDB->f('iForumID');
			$aReturn[$i]['title']	= $this->oDB->f('cTitle');
			$i++;
		}

		return $aReturn;
	}

	/**
	 * Forum::createForum()
	 *
	 * @param string $cName
	 * @param string $cDescription
	 * @param integer $iParent
	 * @param integer $iGroup
	 * @return int
	 */
	public function createForum($cName, $cDescription, $iParent = 0, $iGroup = 0) {
		$iReturn	= false;

		$aWrite		= array($cName, $cDescription, $iParent, $iGroup);
		$this->oDB->write("INSERT INTO forums (cTitle, cDescription, iParentID, iGroupID) VALUES (?, ?, ?, ?)", $aWrite);

		$iReturn	= $this->oDB->insertID();

		return $iReturn;
	}
}
