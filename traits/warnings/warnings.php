<?php
trait Warnings {
	function getWarnings($cExtra = false) {
		$cWarnings	= false;
		$cReturn	= false;

		//get the default warnings
		if (ini_get("register_globals")) { 			$cWarnings	.= "You have register_globals turned on, this is a bad idea, turn it off<br />\n"; }
		if (ini_get("short_tags")) {				$cWarnings	.= "You don't have short tags turned on, it is recommended you turn it on, for no other reason that it makes writing templates easier<br />\n"; }
		if (ini_get("memory_limit") <= 31) {		$cWarnings	.= "Your memory_limit is set to less than 32M it is recommended to have this higher<br />\n"; }
		if (ini_get("post_max_size") <= 8) { 		$cWarnings	.= "Your post_max_size is set to less than 9M it is recommeded you increase this if you plan on creating a file area<br />\n"; }
		if (ini_get("magic_quotes_gpc")) {			$cWarnings	.= "You have magic_quotes_gpc turned on, this is a bad idea, turn it off<br />\n"; }
		if (ini_get("upload_max_filesize") <= 31) {	$cWarnings	.= "Your upload_max_filesize is set to less than 32M it is recommneded you increase this if you plan on creating a file area<br />\n"; }
		if (ini_get("allow_url_include")) {			$cWarnings	.= "You have allow_url_include turned on, it is recommended that you turn this off<br />\n"; }
		if ($cExtra) { 								$cWarnings .= $cExtra; }

		if ($cWarnings) {
			$cReturn	= "<div style=\"width: 100%; background-color: red; color: white; font-size: 1.3em;\">\n";
			$cReturn	.= "<h1>Warnings</h1>";
			$cReturn	.= $cWarnings;

			$cReturn	.= "</div>\n";
		}

		return $cReturn;
	}

	/**
	 * getShiv()
	 *
	 * @return
	 */
	function getShiv() {
		$cReturn  = "<!--[if lt IE 9]>\n";
		$cReturn .= "<script src=\"http://html5shim.googlecode.com/svn/trunk/html5.js\"></script>\n";
		$cReturn .= "<![endif]-->\n";

		return $cReturn;
	}

	function getBanner() {
		$cWarning  = "<!--[if lte IE 8]>\n";
		$cWarning .= "<div style=\"clear: both; height: 59px; padding:0 0 0 15px; position: relative;\">\n";
		$cWarning .= "<a href=\"http://www.microsoft.com/windows/internet-explorer/default.aspx?ocid=ie6_countdown_bannercode\">\n";
		$cWarning .= "<img src=\"http://www.theie6countdown.com/images/upgrade.jpg\" border=\"0\" height=\"42\" width=\"820\" alt=\"IE Upgrade\" />\n";
		$cWarning .= "</a>\n";
		$cWarning .= "</div>\n";
		$cWarning .= "<![endif]-->\n";

		return $cWarning;
	}
}