<?php
/**
 * Layout
 *
 * @package Traits
 * @author Keloran
 * @copyright Copyright (c) 2012
 * @version $Id$
 * @access public
 */
trait Layout {
	/**
	 * addPagination()
	 *
	 * @param mixed $mList
	 * @param mixed $cAddress
	 * @param mixed $iLimit
	 * @param mixed $bStated
	 * @return
	 */
	public function addPagination($mList, $cAddress, $iLimit, $bStated = false) {
		$cReturn 	= false;

		$iPaged		= 0;
		$iItem		= $this->iItem;
		$iPage		= $this->iPage;

		if ($iPage) { $iPaged = $iPage ? $iPage : 0; }
		if ($iItem && !$iPaged) { $iPaged = $iItem ? $iItem : 0; }

		$iCurrentPage 	= $iPaged;

		$cNewAddress 	= $bStated 		? $cAddress . "&amp;num=" 	: $cAddress . "page/";
		$iAmount	= is_array($mList)	? count($mList) 		: $mList;

		if ($iAmount > $iLimit) {
			$j = 2;
			$cReturn  = "<div class=\"pages\">\n";

			//image
			$cReturn .= "<img src=\"/images/layout/pages.png\" alt=\"Pages\" />&nbsp; ";

			//First
			$cReturn .= "<a href=\"" . $cAddress . "\">First</a> || ";

			//if the currentpage and its less than the amount
			if ($iCurrentPage && ($iCurrentPage <= $iAmount)) { $cReturn .= " <a href=\"" . $cNewAddress . ($iCurrentPage - 1) . "\">Back</a> || "; }

			if ($iCurrentPage) {
				$cReturn .= "Page <a href=\"" . $cAddress . "\">1</a>";
			} else {
				$cReturn .= "Page <a class=\"selectedPage\" href=\"" . $cAddress . "\">1</a>";
			}

			for ($i = 0; $i < $iAmount; $i++) {
				if ($i > 0) {
					if ($i % $iLimit == 0) {
						if ($iCurrentPage == $j) {
							$cReturn .= ", <a class=\"selectedPage\" href=\"" . $cNewAddress . $j . "\">" . $j . "</a>";
						} else {
							$cReturn .= ", <a href=\"" . $cNewAddress . $j . "\">" . $j . "</a>";
						}
						$j++;
					}
				}
			}

			//next
			if ($iCurrentPage < $iAmount) {
				if (($iCurrentPage + 1) <= $iAmount) {
					if (($iCurrentPage + 1) == 1) { $iCurrentPage = 1; }
					$cReturn .= " || <a href=\"" . $cNewAddress . ($iCurrentPage + 1) . "\">Next</a> || ";
				}
			}

			//last
			$cReturn .= " || <a href=\"" . $cNewAddress . ($j - 1) . "\">Last</a>";

			//close
			$cReturn .= "</div>";
		}

		$this->setVars("pagination", $cReturn);
		$this->setVars("cPagination", $cReturn);

		return $cReturn;
	}

	/**
	 * fixVars()
	 *
	 * @param array $aVars
	 * @param object $oType
	 * @return null
	 */
	public function fixVars($aVars, $oType) {
		//since set template might have been done before
		if ($aVars) {
			foreach ($aVars as $cKey => $mValue) {
				$oType->setVars($cKey, $mValue);
			}
		}
	}
}