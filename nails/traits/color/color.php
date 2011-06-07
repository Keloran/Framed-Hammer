<?php
trait Traits_Color {
	/**
	 * xml_highlight()
	 *
	 * @desc This is used to highlight xml for printRead
	 * @param string $cXML
	 * @return string
	 */
	function xml_highlight($cXML) {
		$cRegex		= '`(<([a-z]+)([^>]*)>)(.*?)(</\2>)`is';
		$cReplace	= "\1\n\t\4\5";

		$cXML	= preg_replace_callback($cRegex, xml_parse_highlight($cXML), $cXML);

		//Special Chars
		$cXML	= htmlspecialchars($cXML);

		// debug FF00FF

		//Tag <> and values
		$cXML = preg_replace("#&lt;([/]*?)(.*)([\s]*?)&gt;#sU", "<font color=\"#0000FF\">&lt;\\1\\2\\3&gt;</font>", $cXML);

		//Attribute name
		$cXML = preg_replace("#&lt;([\?])(.*)([\?])&gt;#sU", "<font color=\"#800000\">&lt;\\1\\2\\3&gt;</font>", $cXML);

		//Tag Start
		$cXML = preg_replace("#&lt;([^\s\?/=])(.*)([\[\s/]|&gt;)#iU", "&lt;<font color=\"#808000\">\\1\\2</font>\\3", $cXML);

		//Tag End
		$cXML = preg_replace("#&lt;([/])([^\s]*?)([\s\]]*?)&gt;#iU", "&lt;\\1<font color=\"#808000\">\\2</font>\\3&gt;", $cXML);

		//Attribute values
		$cXML = preg_replace("#([^\s]*?)\=(&quot;|')(.*)(&quot;|')#isU", "<font color=\"#800080\">\\1</font>=<font color=\"#D14769\">\\2\\3\\4</font>", $cXML);

		//CDATA
		$cXML = preg_replace("#&lt;(.*)(\[)(.*)(\])&gt;#isU", "&lt;\\1<font color=\"#800080\">\\2\\3\\4</font>&gt;", $cXML);

		//Find the start of the tag, and then find the end of it, so that I can seperate it properlly

		//New Line
		$cXML = preg_replace("#&gt;</font><font color=\"\#0000FF\">&lt;(.*)#isU", "&gt;</font><font color=\"#0000FF\"><br />&nbsp;&nbsp;&lt;\\1", $cXML);
		$cXML = preg_replace("#<br />&nbsp;&nbsp;&lt;/#isU", "<br />&lt;/", $cXML);
		$cXML   = preg_replace("{[\t]+}", "&nbsp;&nbsp;&nbsp;&nbsp;", $cXML);

		return nl2br($cXML);
	}

	/**
	 * xml_parse_highlight()
	 *
	 * @param string $cString
	 * @return string
	 */
	function xml_parse_highlight($cString) {
		$cRegex		= '`(<([a-z]+)([^>]*)>)(.*?)(</\2>)`is';
		$cReplace	= "\1\n\t\4\5";

		return preg_replace($cRegex, $cReplace, $cString);
	}

	/**
	 * sql_highlight()
	 *
	 * @param string $cSQL
	 * @return string
	 */
	function sql_highlight($cSQL) {
		$cStart = "<font color=\"#800000\">";
		$cEnd	= "</font>";

		//SELECT, WHERE
		$cSQL 	= str_ireplace("SELECT", "<font color=\"#0000FF\">SELECT</font>", $cSQL);
		$cSQL	= str_ireplace("WHERE", "<font color=\"#0000FF\">\nWHERE</font>", $cSQL);
		$cSQL 	= str_ireplace("(SELECT", "<font color=\"#0000FF\">(\nSELECT</font>", $cSQL);

		//AND, LIKE
		$cSQL	= str_ireplace("LIKE", "<font color=\"#0000FF\">LIKE</font>", $cSQL);
		$cSQL	= str_ireplace("AND", "<font color=\"#0000FF\">\nAND</font>", $cSQL);

		//FROM, JOIN, LEFT JOIN, RIGHT JOIN
		$cSQL	= str_ireplace('FROM', "<font color=\"#0000FF\">\nFROM</font>", $cSQL);
		$cSQL	= str_ireplace('JOIN', "<font color=\"#0000FF\">\nJOIN</font>", $cSQL);
		$cSQL	= str_ireplace('LEFT JOIN', "<font color=\"#0000FF\">\nLEFT JOIN</font>", $cSQL);
		$cSQL	= str_ireplace('RIGHT JOIN', "<font color=\"#0000FF\">\nRIGHT JOIN</font>", $cSQL);

		//LIMIT, GROUP
		$cSQL	= str_ireplace('LIMIT', "<font color=\"#800080\">\nLIMIT</font>", $cSQL);
		$cSQL	= str_ireplace('GROUP', "<font color=\"#800080\">\nGROUP</font>", $cSQL);

		//BY, AS
		$cSQL	= str_ireplace('BY', "<font color=\"#800080\">BY</font>", $cSQL);
		$cSQL	= str_ireplace('AS', "<font color=\"#800080\">AS</font>", $cSQL);

		//Parenthesses
		$cSQL	= str_ireplace('(', "<font color=\"#800080\">(</font>", $cSQL);
		$cSQL	= str_ireplace(')', "<font color=\"#800080\">)</font>", $cSQL);


		$cFinal	= $cStart . $cSQL . $cEnd;
		return nl2br($cFinal);
	}
}