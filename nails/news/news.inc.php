<?php
/**
 * News
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: news.inc.php 326 2009-12-01 11:39:47Z keloran $
 * @access public
 */
class News implements Nails_Interface {
	//Traits
	use Text, Address;

	private $oUser;
	private $oSession;
	private $oNails;
	private $oDB;
	private $oComments;

	public $iUserLimit	= false;
	public $iNewsLimit	= false;
	public $iQueryTotal	= false;

    private $iNewsID;

    static $oNews;

    /**
     * News::__construct()
     *
     */
	private function __construct(Nails $oNails) {
    	$this->oNails		= $oNails;
		$this->oUser		= $this->oNails->getUser();
		if (!is_object($this->oUser)) { $this->oUser = new User(); } //mainly for IDEs

		$this->oSession		= $this->oNails->getSession();
		if (!is_object($this->oSession)) { $this->oSession = new Session(); }

		$this->iUserLimit	= $this->oUser->getUserLimit();
		$this->iUserID		= $this->oUser->getUserID();

		//setup comments
		$this->oComments				= $this->oNails->getNail("news_comments");
		if (!is_object($this->oComments)) { $this->oComments = new News_Comments(); }

		$this->oComments->iUserLimit	= $this->iUserLimit;
		$this->oComments->iUserID		= $this->iUserID;
		$this->oComments->iArticleID	= $oNails->iItem;
		$this->oComments->iPage			= $oNails->iPage;

		//get teh database object
		if (is_null($this->oDB)) { $this->oDB = $this->oNails->getDatabase(); }
    }

	/**
	 * News::__destruct()
	 *
	 */
	public function __destruct() {
		$this->oUser	= null;
		$this->oSession	= null;
		$this->oNails	= null;
		$this->oDB		= null;
	}

    /**
     * News::getInstance()
     *
     * @return object
     */
    static function getInstance(Nails $oNails) {
    	if (is_null(self::$oNews)) {
    		self::$oNews = new News($oNails);
    	}

    	return self::$oNews;
    }

	/**
	 * News::getMonth()
	 *
	 * @param string $cMonth
	 * @return array
	 */
	public function getMonth($cMonth) {
		$iNewsLimit	= $this->iNewsLimit ? $this->iNewsLimit : 20;
		$iLimit		= $this->iUserLimit ? $this->iUserLimit : $iNewsLimit;
		$iMinLength	= 150;
		$iMonth		= date("m", strtotime($cMonth));
		$i			= 0;
		$aReturn	= false;

		$this->oDB->read("
			SELECT SQL_CALC_FOUND_ROWS
				news.iNewsID,
				news.cTitle,
				news.iUserID,
				FROM_UNIXTIME(news.tsDate, '%d/%m/%Y') AS datePosted,
				FROM_UNIXTIME(news.tsDate, '%Y-%m-%d') AS timeTag,
				news.tsDate,
				news.cContent,
				users.cUsername,
				news_categorys.cCategory,
				news_categorys.iCategoryID,
				news_categorys.cImageName,
				COUNT(news_comments.iCommentID) AS iComments
			FROM news
			LEFT JOIN users ON users.iUserID = news.iUserID
			LEFT JOIN news_categorys ON news_categorys.iCategoryID = news.iCategoryID
			LEFT JOIN news_comments ON news_comments.iNewsID = news.iNewsID
			WHERE FROM_UNIXTIME(news.tsDate, '%m') = ?
			GROUP BY news.iNewsID
			ORDER BY news.iNewsID DESC
			LIMIT " . $iLimit, $iMonth);
		while ($this->oDB->nextRecord()) {
			$aReturn[$i]['iNewsID']		= $this->oDB->f('iNewsID');
			$aReturn[$i]['cTitle']		= ucfirst($this->oDB->f('cTitle'));
			$aReturn[$i]['iPosterID']	= $this->oDB->f('iUserID');
			$aReturn[$i]['cUsername']	= $this->oDB->f('cUsername');
			$aReturn[$i]['cCategory']	= $this->oDB->f('cCategory');
			$aReturn[$i]['iCategoryID'] = $this->oDB->f('iCategoryID');
			$aReturn[$i]['datePosted']	= $this->oDB->f('datePosted');
			$aReturn[$i]['iComments']	= $this->oDB->f('iComments');
			$aReturn[$i]['cImageName']	= $this->oDB->f('cImageName');
			$aReturn[$i]['tsDate']		= $this->oDB->f('tsDate');

			if (strlen($this->oDB->f('cContent')) < $iMinLength) {
				$aReturn[$i]['cContent'] = $this->oDB->f('cContent');
			} else {
				$aReturn[$i]['cContent'] = $this->shrinkThis($this->oDB->f('cContent'), $iMinLength);
			}

			//removed the hungarian to keep inline with other nails, but keep back-compat
			$aReturn[$i]['content'] 	= $aReturn[$i]['cContent'];
			$aReturn[$i]['id']			= $aReturn[$i]['iNewsID'];
			$aReturn[$i]['posterid']	= $aReturn[$i]['iPosterID'];
			$aReturn[$i]['poster']		= $aReturn[$i]['cUsername'];
			$aReturn[$i]['cat']			= $aReturn[$i]['cCategory'];
			$aReturn[$i]['catid']		= $aReturn[$i]['iCategoryID'];
			$aReturn[$i]['dated']		= $aReturn[$i]['datePosted'];
			$aReturn[$i]['title']		= $aReturn[$i]['cTitle'];

			$aReturn[$i]['seotitle']	= $this->makeSEO($aReturn[$i]['title']);
			$aReturn[$i]['timetag']		= $this->oDB->f('timeTag');

			$i++;
		}

		//Now grab the found rows
		$this->oDB->read("SELECT FOUND_ROWS() as rows");
		if ($this->oDB->nextRecord()) {
			$this->iQueryTotal	= $this->oDB->f('rows');
		}

		return $aReturn;
	}

    /**
     * News::getLatest()
     *
     * @param string $cExtraWhere Incase you need an extra where clause
     * @return mixed
     */
    public function getLatest($cExtraWhere = false, $iMinLength = false, $cExtraJoin = false) {
    	$iNewsLimit = $this->iNewsLimit ? $this->iNewsLimit : 20;
        $iLimit 	= $this->iUserLimit	? $this->iUserLimit : $iNewsLimit;
        $cWhere 	= $cExtraWhere		? $cExtraWhere		: "";
        $cJoin		= $cExtraJoin		? $cExtraJoin  		: "";
    	$iMinLength	= $iMinLength		? $iMinLength		: 150;
    	$iMin 		= $iMinLength - 30;
    	$iMax 		= $iMinLength + 10;
    	$i			= 0;
		$cFinalCont	= "";
		$aReturn	= false;

        $this->oDB->read("
            SELECT SQL_CALC_FOUND_ROWS
                news.iNewsID,
                news.cTitle,
                news.iUserID,
				FROM_UNIXTIME(news.tsDate, '%d/%m/%Y') AS datePosted,
				FROM_UNIXTIME(news.tsDate, '%Y-%m-%d') AS timeTag,
				news.tsDate,
                news.cContent,
                users.cUsername,
                news_categorys.cCategory,
                news_categorys.iCategoryID,
				news_categorys.cImageName,
				COUNT(news_comments.iCommentID) AS iComments
            FROM news
            LEFT JOIN users ON users.iUserID = news.iUserID
            LEFT JOIN news_categorys ON news_categorys.iCategoryID = news.iCategoryID
			LEFT JOIN news_comments ON news_comments.iNewsID = news.iNewsID " . $cJoin . "
			WHERE news.tsDate < UNIX_TIMESTAMP() " . $cWhere . "
			GROUP BY news.iNewsID
            ORDER BY news.iNewsID DESC
            LIMIT " . $iLimit);
        while ($this->oDB->nextRecord()) {
            $aReturn[$i]['iNewsID']		= $this->oDB->f('iNewsID');
            $aReturn[$i]['cTitle']		= ucfirst($this->oDB->f('cTitle'));
            $aReturn[$i]['iPosterID']	= $this->oDB->f('iUserID');
            $aReturn[$i]['cUsername']	= $this->oDB->f('cUsername');
            $aReturn[$i]['cCategory']	= $this->oDB->f('cCategory');
            $aReturn[$i]['iCategoryID'] = $this->oDB->f('iCategoryID');
			$aReturn[$i]['datePosted']	= $this->oDB->f('datePosted');
			$aReturn[$i]['iComments']	= $this->oDB->f('iComments');
			$aReturn[$i]['cImageName']	= $this->oDB->f('cImageName');
			$aReturn[$i]['tsDate']		= $this->oDB->f('tsDate');

            if (strlen($this->oDB->f('cContent')) < $iMinLength) {
                $aReturn[$i]['cContent'] = $this->oDB->f('cContent');
            } else {
            	$aReturn[$i]['cContent'] = $this->shrinkThis($this->oDB->f('cContent'), $iMinLength);
            }

            //removed the hungarian to keep inline with other nails, but keep back-compat
            $aReturn[$i]['content'] 	= $aReturn[$i]['cContent'];
            $aReturn[$i]['id']			= $aReturn[$i]['iNewsID'];
            $aReturn[$i]['posterid']	= $aReturn[$i]['iPosterID'];
            $aReturn[$i]['poster']		= $aReturn[$i]['cUsername'];
            $aReturn[$i]['cat']			= $aReturn[$i]['cCategory'];
            $aReturn[$i]['catid']		= $aReturn[$i]['iCategoryID'];
            $aReturn[$i]['dated']		= $aReturn[$i]['datePosted'];
            $aReturn[$i]['title']		= $aReturn[$i]['cTitle'];

        	$aReturn[$i]['seotitle']	= $this->makeSEO($this->oDB->f('cTitle'));
        	$aReturn[$i]['timetag']		= $this->oDB->f('timeTag');

			$i++;
        }

		//Now grab the found rows
		$this->oDB->read("SELECT FOUND_ROWS() as rows");
		if ($this->oDB->nextRecord()) {
			$this->iQueryTotal	= $this->oDB->f('rows');
		}

		return $aReturn;
    }

    /**
     * News::getComments()
     *
     * @return array
     */
	public function getComments() {
		$aReturn = $this->oComments->getComments();

		return $aReturn;
    }

	/**
	 * News::getNewsID()
	 *
	 * @return int
	 */
	private function getNewsID() {
		$iReturn	= false;
		if ($this->oNails->cChoice) {
			$cTitle = unSEO($this->oNails->cChoice);

			$this->oDB->read("SELECT iNewsID FROM news WHERE cTitle = ? LIMIT 1", $cTitle);
			if ($this->oDB->nextRecord()) {
				$iReturn = $this->oDB->f('iNewsID');
			}
		}

		return $iReturn;
	}

	/**
	 * News::getTitle()
	 *
	 * @return string
	 */
	public function getTitle() {
		return unSEO($this->oNails->cChoice);
	}

	/**
	 * News::getNews()
	 *
	 * @return mixed
	 */
	public function getNews($iNewsID = false) {
		if (!$this->oNails->iItem && !$iNewsID) { $iNewsID = $this->getNewsID(); } //get the id based on the title
		if (!$iNewsID) { return false; } //no id at all return false;

        $this->iNewsID	= $this->oNails->iItem  ? $this->oNails->iItem : $iNewsID;
        $aReturn		= false;

		$this->oDB->read("
			SELECT
                news.iNewsID,
                news.cTitle,
                news.iUserID,
				news.cContent,
				FROM_UNIXTIME(news.tsDate, '%d/%m/%Y') as datePosted,
				FROM_UNIXTIME(news.tsDate, '%Y-%m-%d') as timeTag,
                users.cUsername,
                news_categorys.cCategory,
                news_categorys.iCategoryID,
				news_categorys.cImageName,
				COUNT(news_comments.iCommentID) as numComments
            FROM news
            LEFT JOIN users ON users.iUserID = news.iUserID
            LEFT JOIN news_categorys ON news_categorys.iCategoryID = news.iCategoryID
			LEFT JOIN news_comments ON news_comments.iNewsID = news.iNewsID
			WHERE news.iNewsID = ?
			GROUP BY news.iNewsID
            ORDER BY news.iNewsID DESC
            LIMIT 1", $this->iNewsID);
		$i = 0;
        if ($this->oDB->nextRecord()) {
            $aReturn['iNewsID']     = $this->oDB->f('iNewsID');
            $aReturn['cTitle']      = ucfirst($this->oDB->f('cTitle'));
            $aReturn['iPosterID']   = $this->oDB->f('iUserID');
            $aReturn['cUsername']   = $this->oDB->f('cUsername');
            $aReturn['cCategory']   = $this->oDB->f('cCategory');
            $aReturn['iCategoryID'] = $this->oDB->f('iCategoryID');
			$aReturn['datePosted']  = $this->oDB->f('datePosted');
			$aReturn['cContent']    = stripslashes($this->oDB->f('cContent'));
			$aReturn['cImageName']  = $this->oDB->f('cImageName');
			$aReturn['numComments'] = $this->oDB->f('numComments');

			//removed the hungarian to bring inline with other nails
			$aReturn['id']			= $aReturn['iNewsID'];
			$aReturn['title']		= $aReturn['cTitle'];
			$aReturn['poster']		= $aReturn['cUsername'];
			$aReturn['posterid']	= $aReturn['iPosterID'];
			$aReturn['catid']		= $aReturn['iCategoryID'];
			$aReturn['content']		= $aReturn['cContent'];
			$aReturn['comments']	= $aReturn['numComments'];
			$aReturn['dated']		= $aReturn['datePosted'];

        	$aReturn['seotitle']	= $this->makeSEO($aReturn['title']);
        	$aReturn['timetag']		= $this->oDB->f('timeTag');
        }

        return $aReturn;
	}

	/**
	 * News::insertNews()
	 *
	 * @param string $cTitle
	 * @param int $iCategory
	 * @param string $cContent
	 * @param timestamp $tsLiveDate
	 * @return int
	 */
	public function insertNews($cTitle, $cContent, $iCategoryID = false, $tsLiveDate = false) {
		if (!$this->iUserID) { return false; }

		$tsDate		= $tsLiveDate	? $tsLiveDate	: time();
		$iCategory	= $iCategoryID	? $iCategoryID	: 1;

		$aEscape	= array($cTitle, $cContent, $tsDate, $this->iUserID, $iCategoryID);
		$this->oDB->write("INSERT INTO `news` (cTitle, cContent, tsDate, iUserID, iCategoryID) VALUES (?, ?, ?, ?, ?)", $aEscape);

		$iNewsID = $this->oDB->insertID();

		$this->oSession->addAction("Added News: " . $this->oDB->insertID());
		return $iNewsID;
	}

    /**
     * News::addCategory()
     *
     * @param string $cCategory
     * @param string $cCatImage
     * @return null
     */
    public function addCategory($cCategory, $cCatImage) {
        $aInsert = array($cCategory, $cCatImage);
        $this->oDB->write("INSERT INTO `news_categorys` (cCategory, cImageName) VALUES (?, ?)", $aInsert);

		$this->oSession->addAction("Added Category: " . $this->oDB->insertID());
        $this->oNails->sendLocation("/admin/news/");
    }

    /**
     * News::addComment()
     *
     * @param string $cComment
     * @return mixed
     */
	public function addComment($cComment, $cName = false) {
    	$this->oComments->addComment($cComment, $cName);
    }

    /**
     * News::getArchive()
     *
     * @param string $cExtraWhere Incase you need an extra where
     * @return mixed
     */
    public function getArchive($cExtraWhere = false) {
        $cWhere 	= $cExtraWhere 				? $cExtraWhere 							: false;
        $iLimit		= $this->iUserLimit			? $this->iUserLimit						: 20;
        $iPage		= $this->oNails->iPage		? $this->oNails->iPage - 1 * $iLimit	: 0;
        $aReturn	= false;

		$aRead	= array($iPage, $iLimit);
        $this->oDB->read("
            SELECT SQL_CALC_FOUND_ROWS
                news.iNewsID,
                news.cTitle,
                news.iUserID,
                FROM_UNIXTIME(news.tsDate, '%d/%m/%Y') AS datePosted,
                FROM_UNIXTIME(news.tsDate, '%Y-%m-%d') AS timeTag,
                users.cUsername,
                COUNT(news_comments.iCommentID) as numComments
           FROM news
           LEFT JOIN users ON users.iUserID = news.iUserID
           LEFT JOIN news_comments ON news_comments.iNewsID = news.iNewsID
           " . $cWhere . "
           GROUP BY news.iNewsID
           ORDER BY news.iNewsID DESC
           LIMIT ?, ?", $aRead);
        $i = 0;
        while ($this->oDB->nextRecord()) {
            $aReturn[$i]['id']      	= $this->oDB->f('iNewsID');
            $aReturn[$i]['title']   	= $this->oDB->f('cTitle');
            $aReturn[$i]['date']    	= $this->oDB->f('datePosted');
            $aReturn[$i]['poster']  	= $this->oDB->f('cUsername');
            $aReturn[$i]['posterid']	= $this->oDB->f('iUserID');
            $aReturn[$i]['comments']	= $this->oDB->f('numComments');

			$aReturn[$i]['seotitle']	= $this->makeSEO($aReturn[$i]['title']);
        	$aReturn[$i]['timetag']		= $this->oDB->f('timeTag');

            //brings inline with other nails
            $aReturn[$i]['dated']	= $aReturn[$i]['date'];

            $i++;
        }

        //Get the pagination
        $this->oDB->read("SELECT FOUND_ROWS() AS rows");
        if ($this->oDB->nextRecord()) {
        	$this->iQueryTotal = $this->oDB->f('rows');
        }

        return $aReturn;
    }

	/**
	 * News::getArchiveNew()
	 *
	 * @param string $cExtraWhere
	 * @return array
	 */
	public function getArchiveNew($cExtraWhere = false) {
		$cWhere	= $cExtraWhere 			? $cExtraWhere 							: null;
		$iLimit	= $this->iUserLimit		? $this->iUserLimit						: 20;
		$iPage	= $this->oNails->iPage	? $this->oNails->iPage - 1 * $iLimit	: 0;

		$i 			= 0;
		$k			= 0;
		$aReturn	= false;

		$aResult1 = array();
		$aResult2 = array();
		$aResult3 = array();
		$aResult4 = array();

		$aRead	= array($iPage, $iLimit);
		$this->oDB->read("
			SELECT
				news.iNewsID,
				news.cTitle,
				news.iUserID,
				FROM_UNIXTIME(news.tsDate, '%d/%m/%Y') AS datePosted,
				FROM_UNIXTIME(news.tsDate, '%M') AS dateMonth,
				FROM_UNIXTIME(news.tsDate, '%Y-%m-%d') AS timeTag,
				users.cUsername,
				COUNT(news_comments.iCommentID) AS numComments
			FROM news
			LEFT JOIN users ON users.iUserID = news.iUserID
			LEFT JOIN news_comments ON news_comments.iNewsID = news.iNewsID
			GROUP BY news.iNewsID
			ORDER BY news.iNewsID DESC
			LIMIT ?, ?", $aRead);
		while ($this->oDB->nextRecord()) {
			//split the months
			$aResult[$i]['month']	= $this->oDB->f('dateMonth');

			$aResult[$i]['items']['title']		= $this->oDB->f('cTitle');
			$aResult[$i]['items']['username']	= $this->oDB->f('cUsername');
			$aResult[$i]['items']['id']			= $this->oDB->f('iNewsID');
			$aResult[$i]['items']['dated']		= $this->oDB->f('datePosted');
			$aResult[$i]['items']['comments']	= $this->oDB->f('numComments');

			$aResult[$i]['items']['seotitle']	= $this->makeSEO($aResult[$i]['items']['title']);
			$aResult[$i]['items']['timetag']	= $this->oDB->f('timeTag');

			$i++;
		}

		//no result
		if (!$aResult) { return false; }

		//meld them array
		$j = 0;
		for ($i = 0; $i < count($aResult); $i++) {
			$cMonth = $aResult[$i]['month'];

			$aResult2[$cMonth][$j]	= $aResult[$i]['items'];
			$j++;
		}

		//now turn into nice array
		$i = 0;
		foreach ($aResult2 as $month => $items) {
			$aResult3[$i]['month']	= $month;
			$aResult3[$i]['items']	= $items;
			$i++;
		}

		$aReturn = $aResult3;

		return $aReturn;
	}

	/**
	 * News::getArchiveMonths()
	 *
	 * @return array
	 */
	public function getArchiveMonths() {
		$aReturn	= false;
		$i			= 0;

		$this->oDB->read("SELECT DISTINCT FROM_UNIXTIME(news.tsDate, '%M') AS month FROM news LIMIT 12");
		while ($this->oDB->nextRecord()) {
			$aReturn[$i]['month']	= $this->oDB->f('month');
			$i++;
		}

		return $aReturn;
	}

    /**
     * News::updateNews()
     *
     * @param string $cContent
     * @param string $cTitle
     * @param bool $bNoUpdate This stops it showing "Updated on"
     * @return null
     */
    public function updateNews($cContent, $cTitle = false, $iNewsID = false, $bNoUpdate = false) {
        $iUpdateUserID		= $this->oUser->getUserID();
        $cUpdateUserName	= $this->oUser->getUserName();
       	$iNews			= $this->oNails->iItem ? $this->oNails->iItem : $iNewsID;

		if (!$bNoUpdate) {
	        $cContent .= "<br />Updated By <a href=\"/users/viewprofile/" . $iUpdateUserID . "/\">". $cUpdateUserName . "</a>";
		}

        if ($cTitle) {
        	$aEscape = array($cContent, $cTitle, $iNews);
        	$this->oDB->write("UPDATE news SET `cContent` = ?, `cTitle` = ? WHERE iNewsID = ? LIMIT 1", $aEscape);
        } else {
        	$aEscape = array($cContent, $iItem);
        	$this->oDB->write("UPDATE news SET `cContent` = ? WHERE iNewsID = ? LIMIT 1", $aEscape);
        }

	$this->oSession->addAction("Updated News: " . $this->oNails->iItem);
        $this->oNails->sendLocation("refer");
    }

    /**
     * News::getList()
     *
     * @desc Get the full list of the news for the admin area
     * @return array
     */
    public function getList() {
    	$aReturn	= false;
    	$i			= 0;

    	$this->oDB->read("SELECT iNewsID, cTitle, tsDate FROM news ORDER BY iNewsID DESC");
    	while ($this->oDB->nextRecord()) {
    		$aReturn[$i]['id']		= $this->oDB->f('iNewsID');
    		$aReturn[$i]['title']	= $this->oDB->f('cTitle');
			$aReturn[$i]['dated']	= $this->oDB->f('tsDate');
    		$i++;
    	}

    	return $aReturn;
    }
}
