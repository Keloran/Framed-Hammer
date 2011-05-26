<?php
/**
 * Footer
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Footer implements Nails_Interface {
	//This gets set during the construct
	private $oNails;
	private $oDB;

	static $oFooter;

	/**
	 * Footer::__construct()
	 *
	 * @param Nails $oNails
	 */
	private function __construct(Nails $oNails, $bNoInstall = null) {
		if (!$bNoInstall) {
			$this->oNails	= $oNails;
			$this->oDB		= $oNails->getDatabase();
		}
	}

	/**
	 * Footer::__destruct()
	 *
	 */
	public function __destruct() {
		$this->oNails	= null;
		$this->oDB		= null;
	}

	/**
	 * Footer::getInstance()
	 *
	 * @param object $oNails
	 * @return object
	 */
	static function getInstance(Nails $oNails, $bNoInstall = null) {
		if (is_null(self::$oFooter)) {
			self::$oFooter	= new Footer($oNails, $bNoInstall);
		}

		return self::$oFooter;
	}

	/**
	 * Footer::getVersion()
	 *
	 * @desc This doesnt really work atm, and is unlikelly to ever work
	 * @return string
	 */
	public function getVersion() {
		$cReturn = "1.1";

		return $cReturn;
	}

	/**
	 * Footer::getShareThis()
	 *
	 * @return string
	 */
	public function getShareThis() {
		$aShareThis	= $this->oNails->getConfig("shareThis");
		$cReturn	= false;

		if ($aShareThis) {
			$cShareThis	= $aShareThis['code'];
			$cStyle		= $aShareThis['style'];

			$cReturn	 = "<div id=\"shareThis\">";
			$cReturn	.= "<script type=\"text/javascript\" src=\"http://w.sharethis.com/button/sharethis.js#publisher=";
			$cReturn	.= $cShareThis;
			$cReturn	.= "&amp;type=website&amp;style=";
			$cReturn	.= $cStyle;
			$cReturn	.= "\"></script>";
			$cReturn	.= "</div>";
		}

		return $cReturn;
	}

	/**
	 * Footer::getReInvigorate()
	 *
	 * @desc This gets the re-invigorate tracking stuff, also including username, and action tracking
	 * @return string
	 */
	public function getReInvigorate() {
		$cCode		= $this->oNails->getConfig("code", "reinvigorate");
		$cReturn	= false;

		if ($cCode) {
			$oUser		= $this->oNails->getUser();
			$cUserName	= $oUser->getUserName() ? $oUser->getUserName() : false;

			//opener for the tracker, and the code itself
			$cReturn	 = "<script type=\"text/javascript\" src=\"http://include.reinvigorate.net/re_.js\"></script>\n";
			$cReturn	.= "<script type=\"text/javascript\">\n";
			$cReturn	.= "try {\n";
			//there is username
			if ($cUserName) { $cReturn	.= "var re_name_tag =\"" . $cUserName . "\";\n"; }

			//if the page is register
			if ($this->oNails->cAction == "register" || $this->oNails->cPage == "register") { $cReturn .= "var re_new_user_tag = true;\n"; }

			//if the page is comment
			if ($this->oNails->cAction == "comment" || $this->oNails->cChoice == "comment") { $cReturn .= "var re_comment_tag = true;\n"; }

			//if the page is checkout
			if ($this->oNails->cAction == "checkout" || $this->oNails->cChoice == "checkout" || $this->oNails->cPage == "checkout") { $cReturn .= "var re_purchase_tag = true;\n"; }

			//the tracking code, has to be done after giving the variables
			$cReturn	.= "re_(\"" . $cCode . "\")\n";

			//closer of the tracker
			$cReturn	.= "} catch (err) {}\n";
			$cReturn	.= "</script>\n";
		}

		return $cReturn;
	}

	/**
	 * Footer::getGoogleAnalytics()
	 *
	 * @return string
	 */
	public function getGoogleAnalytics($bOld = null) {
		$cCode		= $this->oNails->getConfig("analytics");
		$cDomain	= $this->oNails->getConfig("analytics-domain");
		$cReturn	= false;

		//new google code
		if ($cCode) {
			$cReturn	 = "<script type=\"text/javascript\">\n";
			$cReturn	.= "var _gaq = _gaq || [];\n";
			$cReturn	.= "_gaq.push(['_setAccount', '" . $cCode . "']);\n";

			//e.g. given bugs.com and needs www.bugs.com aswell
			if ($cDomain) { $cReturn .= "_gaq.push(['_setDomainName', '" . $cDomain . "']);\n"; }

			$cReturn	.= "_gaq.push(['_trackPageview']);\n";
			$cReturn	.= "(function() {\n";
			$cReturn	.= "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n";
			$cReturn	.= "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n";
			$cReturn	.= "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n";
			$cReturn	.= "})();\n";
			$cReturn	.= "</script>\n";
		}

		if ($bOld) {
			//old google code
			if ($cCode) {
				$cReturn	 = "<script type=\"text/javascript\">\n";
				$cReturn	.= "var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");\n";
				$cReturn	.= "document.write(unescape\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));\n";
				$cReturn	.= "</script>\n";
				$cReturn	.= "<script type=\"text/javascript\">\n";
				$cReturn 	.= "try {\n";
				$cReturn	.= "var pageTracker = _gat._getTracker(\"" . $cCode . "\");\n";
				$cReturn	.= "pageTracker._trackPageview();\n";
				$cReturn	.= "} catch(err) {}</script>\n";
			}
		}

		return $cReturn;
	}

	/**
	* Footer::getFoot();
	*
	* @desc This returns the footer, which atm is just the closer for body/html tags
	* @param bool $bShare This is incase we want to call the sharethis somewhere else
	* @return string
	*/
	public function getFoot($bShare = null) {
		if ($bShare) {
			$cReturn = $this->getShareThis();
		} else {
			$cReturn = "";
		}

		$cReturn .= "</body>\n";
		$cReturn .= "</html>\n";

		//if there is no ob make one, \
		//this is to stop notices when a notice has already been thrown
		if (!ob_get_level()) { ob_start(); }

		return $cReturn;
	}
}
