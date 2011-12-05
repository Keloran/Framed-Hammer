<?php
/**
 * Search
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: search.inc.php 63 2009-09-22 09:06:11Z keloran $
 * @access public
 */
class Search implements Nails_Interface {
	private $oNails;
	private $oDB;

	private static $oSearch;

    /**
     * Search::__construct()
     *
     */
    private function __construct(Nails $oNails) {
    	$this->oNails	= $oNails;
    	$this->oDB		= $oNails->getDatabase();
    }

    /**
     * Search::getInstance()
     *
     * @param Nails $oNails
     * @return object
     */
    static function getInstance(Nails $oNails) {
    	if (is_null(self::$oSearch)) {
    		self::$oSearch = new Search($oNails);
    	}

    	return self::$oSearch;
    }

    /**
     * Search::findUser()
     *
     * @param mixed $mUser
     * @return array
     */
    public function findUser($mUser) {
    	$aReturn	= false;

        if (is_int($mUser)) {
            $cWhere = " WHERE iUserID = ? ";
            $mWhere = $mUser;
        } else if (is_string($mUser)) {
            $cWhere = " WHERE cUsername = ? ";
            $mWhere = $mUser;
        }

        $this->oDB->read("SELECT iUserID, cUsername FROM users " . $cWhere . " LIMIT 1", $mWhere);
        if ($this->oDB->nextRecord()) {
        	$aReturn['id']		= $this->oDB->f('iUserID');
        	$aReturn['name']	= $this->oDB->f('cUsername');
        }

        return $aReturn;
    }

    /**
     * Search::findNew()
     *
     * @param string $cSearch
     * @param int $iType
     * @return
     */
    public function findNews($cSearch, $iType) {
    	$aReturn	= false;
    	$i			= 0;

    	switch ($iType) {
			case 1: //Title
				$cSQL		= " cTitle LIKE ? ";
				$mSearch	= "%" . $cSearch . "%";
				break;

			case 2: //Content
				$cSQL		= " cContent LIKE ? ";
				$mSearch	= "%" . $cSearch . "%";
				break;

			case 3:
			default: //All
				$cSQL		= " cTitle LIKE ? OR cContent LIKE ? ";
				$cSearch	= "%" . $cSearch . "%";
				$mSearch	= array($cSearch, $cSearch);
				break;
		}

		$this->oDB->read("SELECT iNewsID, cTitle, cContent, FROM_UNIXTIME(tsDate, '%d/%m/%Y') as dated FROM news WHERE " . $cSQL . " ORDER BY iNewsID DESC, tsDate ASC", $mSearch);
		while ($this->oDB->nextRecord()) {
			$aReturn[$i]['id']		= $this->oDB->f('iNewsID');
			$aReturn[$i]['title']	= $this->oDB->f('cTitle');
			$aReturn[$i]['dated']	= $this->oDB->f('dated');
			$aReturn[$i]['content']	= $this->oDB->f('cContent');
			$i++;
		}

		return $aReturn;
    }

    /**
     * Search::searchForum()
     *
     * @param string $cSearch
     * @param int $iType
     * @return
     */
    public function searchForum($cSearch, $iType) {
    	$aReturn	= false;
    	$i			= 0;

    	//search
    	$mSearch	= "%" . $cSearch . "%";
    	$cSearch	= $mSearch;

    	switch ($iType) {
    		case 1: //Title
    			$cSQL	= " FROM forums_topics WHERE cTitle LIKE ? ";
    			break;

    		case 2: //content
    			$cSQL	= " FROM forums_topics WHERE cContent LIKE ? ";
    			break;

    		case 3: //replys
    			$cSQL	= " FROM forums_replys WHERE cContent LIKE ? ";
    			break;

    		default:
    		case 4: //All
    			$cSQL	= " forums_replys.cContent as reply
    				FROM forums_topics
    				LEFT JOINS forums_replys ON forums_replys.iTopicID = forums_topic.iTopicID
    				WHERE forums_topics.cContent LIKE ?
    				OR forums_replys.cContent LIKE ? ";
    			$mSearch = array($cSearch, $cSearch);
    			break;
    	}

    	$this->oDB->read("SELECT forums_topics.cContent, forums_topics.cTitle, forums_topics.iTopicID " . $cSQL . " ORDER BY iTopicID DESC", $mSearch);
    	while ($this->oDB->nextRecord()) {
    		$aReturn[$i]['title']	= $this->oDB->f('cTitle');
    		$aReturn[$i]['content']	= $this->oDB->f('ccontent');
    		$aReturn[$i]['reply']	= $this->oDB->f('reply');
    		$aReturn[$i]['id']		= $this->oDB->f('iTopicID');
    		$i++;
    	}

    	return $aReturn;
    }

	/**
	 * Search::searchUnknown()
	 *
	 * @param string $cTable
	 * @param mixed $mResultFields
	 * @param mixed $mSearchFields
	 * @param string $cSearch
	 * @return array
	 */
	public function searchUnknown($cTable, $mResultFields, $mSearchFields, $cSearch) {
		$aReturn	= null;
		$z			= 0;

		//the resultfields
		$cResultFields	= false;
		if (is_array($mResultFields)) {
			for ($i = 0; $i < count($mResultFields); $i++) {
				if ($i == 0) {
					$cResultFields .= " ";
				} else {
					$cResultFields .= ", ";
				}

				$cResultFields .= $mResultFields[$i];
			}
		} else {
			$cResultFields = $mResultFields;
		}

		//the search fields
		$cSearchFields	= false;
		if (is_array($mSearchFields)) {
			for ($i = 0; $i < count($mSearchFields); $i++) {
				if ($i == 0) {
					$cSearchFields .= " WHERE ";
				} else {
					$cSearchFields .= " AND ";
				}

				if (strstr($mSearchFields[$i], "=")) {
					$cSearchFields .= $mSearchFields[$i];
				} else {
					$cSearchFields .= $mSearchFields[$i] . " = ? ";
				}
			}
		} else {
			$cSearchFields = " WHERE " . $mSearchFields . " = ? ";
		}

		$cSQL = "SELECT " . $cResultFields . " FROM " . $cTable . $cSearchFields;
		$this->oDB->read($cSQL, $cSearch);
		while ($this->oDB->nextRecord()) {
			for ($i = 0; $i < count($aResultFields); $i++) {
				$cResult = $aResultFields[$i];
				$aReturn[$z][$cResult]	= $this->oDB->f($cResult);
			}

			$z++;
		}

		$this->addSearch($cSearch, $cSQL);

		return $aReturn;
	}

	/**
	 * Search::addSearch()
	 *
	 * @param string $cSearch
	 * @param string $cQuery
	 * @return null
	 */
	private function addSearch($cSearch, $cQuery) {
		$cSource = false;

		if ($this->oNails->aParams) {
			$cSource = serialize($this->oNails->aParams);
		}

		$aInsert = array($cSearch, $cQuery, $cSource);
		$this->oDB->write("INSERT INTO search (cSearch, dDated, cSearchQuery, cSource) VALUES (?, NOW, ?, ?)", $aInsert);
	}

	/**
	 * Search::getSearches()
	 *
	 * @param int $iLimit
	 * @return array
	 */
	public function getSearches($iLimit = null) {
		$aReturn	= false;
		$i			= 0;
		$iLimit		= ($iLimit ? $iLimit : 20);

		$this->oDB->read("SELECT cSearch, bDated FROM search ORDER BY iSearchID DESC LIMIT " . $iLimit);
		while ($this->oDB->nextRecord()) {
			$aReturn[$i]['search']	= $this->oDB->f('cSearch');
			$aReturn[$i]['dated']	= $this->oDB->f('bDated');
			$i++;
		}

		return $aReturn;
	}
}