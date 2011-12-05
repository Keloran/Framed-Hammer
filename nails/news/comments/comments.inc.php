<?php
/**
 * News_Comments
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class News_Comments implements Nails_Interface {
	private $oNails;
	private $oDB;
	private $oSession;

	public $iPage;
	public $iArticleID;
	public $iNewsID;

	public $iUserID;
	public $iUserLimit;

	private static $oComments;

	/**
	 * News_Comments::__construct()
	 *
	 * @param Nails $oNails
	 */
	private function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB	= $oNails->getDatabase();
		$this->oSession	= $oNails->getSession();
		$this->iNewsID	= $this->iArticleID;
	}

	/**
	 * News_Comments::getInstance()
	 *
	 * @param Nails $oNails
	 * @return object
	 */
	public static function getInstance(Nails $oNails) {
		if (is_null(self::$oComments)) {
			self::$oComments = new News_Comments($oNails);
		}

		return self::$oComments;
	}

	/**
	 * News_Comments::addComment()
	 *
	 * @param string $cComment
	 * @param string $cName
	 * @return null
	 */
	public function addComment($cComment, $cName = null) {
		//Does the system require login
		$bOpenComments	= $this->oNails->getConfig("comments");
		if ($bOpenComments) {
			$iUserID	= 0;
		} else {
			$iUserID	= $this->iUserID;
		}

		if ($cName) {
			$aInsert = array($cComment, $this->iArticleID, $cName);
			$this->oDB->write("INSERT INTO `news_comments` (cComment, iNewsID, cName, tsDate) VALUES (?, ?, ?, UNIX_TIMESTAMP())", $aInsert);
		} else {
			$aInsert = array($cComment, $this->iArticleID, $iUserID);
			$this->oDB->write("INSERT INTO `news_comments` (cComment, iNewsID, iUserID, tsDate) VALUES (?, ?, ?, UNIX_TIMESTAMP())", $aInsert);
		}

		$this->oSession->addAction("Added News Comment: " . $this->oDB->insertID());
		$this->oNails->sendLocation("/news/comments/" . $this->iArticleID . "/" . $this->oDB->insertID());
	}

	/**
	 * News_Comments::getComments()
	 *
	 * @return array
	 */
	public function getComments() {
		if (!$this->iArticleID) { return false; }

		$iLimit		= $this->iUserLimit	? $this->iUserLimit				: 20;
		$iPage		= $this->iPage		? $this->iPage - 1 * $iLimit	: 0;
		if ($iPage < 0) { $iPage = 0; } //Stop wierd bug that can cause this to have a negative number

		$i			= 0;
		$aReturn	= false;

		$aRead		= array($this->iArticleID, $iPage, $iLimit);
		$this->oDB->read("
			SELECT SQL_CALC_FOUND_ROWS
				news_comments.iCommentID,
				news_comments.iUserID,
				news_comments.tsDate,
				news_comments.cComment,
				FROM_UNIXTIME(news_comments.tsDate, '%d/%m/%Y') as datePosted,
				users.cUsername
			FROM news_comments
			LEFT JOIN users ON users.iUserID = news_comments.iUserID
			WHERE news_comments.iNewsID = ?
			ORDER BY news_comments.iCommentID DESC
			LIMIT ?, ?", $aRead);
		while ($this->oDB->nextRecord()) {
			if (($i % 2) == 0) {
				$aReturn[$i]['class'] = "odd";
			} else {
				$aReturn[$i]['class'] = "even";
			}

			$aReturn[$i]['iCommentID'] = $this->oDB->f('iCommentID');
			$aReturn[$i]['iPosterID'] = $this->oDB->f('iUserID');
			$aReturn[$i]['datePosted'] = $this->oDB->f('datePosted');
			$aReturn[$i]['cUsername'] = $this->oDB->f('cUsername');
			$aReturn[$i]['cComment'] = $this->oDB->f('cComment');

			//removed the hugarian, to bring inline with other nails
			$aReturn[$i]['id']			= $aReturn[$i]['iCommentID'];
			$aReturn[$i]['posterid']	= $aReturn[$i]['iPosterID'];
			$aReturn[$i]['poster']		= $aReturn[$i]['cUsername'];
			$aReturn[$i]['content']		= $aReturn[$i]['cComment'];
			$aReturn[$i]['dated']		= $aReturn[$i]['datePosted'];

			$i++;
		}

		//Get the amount of comments
		$this->oDB->read("SELECT FOUND_ROWS() AS rows");
		if ($this->oDB->nextRecord()) {
			$this->iQueryTotal = $this->oDB->f('rows');
		}

		return $aReturn;
	}
}
