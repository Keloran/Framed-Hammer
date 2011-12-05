<?php
/**
 * Validator
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: validator.inc.php 478 2010-01-04 13:51:50Z keloran $
 * @access public
 */
class Validator {
	static $oValidator;

	private	$oForm;

	//This is for portability
	public $oNails;

	/**
	 * Validator::__construct()
	 *
	 */
	private function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
	}

	/**
	 * Validator::getInstance()
	 *
	 * @param object $oForm This has to be a Form class, otherwise it cant be intialized
	 * @return object
	 */
	static function getInstance(Nails $oNails) {
		//i always want a new instance
		self::$oValidator		= new Validator($oNails);

		return self::$oValidator;
	}

	/**
	 * Validator::email()
	 *
	 * @desc This takes the email and checks if its valid, if you have filter_var
	 * then it will use built in filters
	 * @param string $cEmail
	 * @return string
	 */
	public function email($cEmail) {
		$cReturn	= false;

		//Might aswell use filter var if its avalible, less resource-hungry
		if (function_exists("filter_var")) {
			$cReturn	= filter_var($cEmail, FILTER_VALIDATE_EMAIL);
		} else {
			$cPattern = "([\\w-+]+(?:\\.[\\w-+]+)*@(?:[\\w-]+\\.)+[a-zA-Z]{2,7})";
			if (preg_match($cPattern, $cEmail)) {
				$cReturn = $cEmail;
			}
		}

		return $cReturn;
	}

	/**
	 * Validator::normalConvert()
	 *
	 * @param string $cText
	 * @param int $iLoop
	 * @return
	 */
	private function normalConvert($cText, $iLoop = false) {
		//k get rid of bloddy \r's
		$cText = str_replace("\r\n", "\n", $cText);
		$cText = str_replace("\r", null, $cText);

		//this is to stop wierd things getting sent e.g. <script> tags
		$cText = htmlentities($cText, ENT_QUOTES);

		//convert these bbcodes
		$aPatterns = array(
			//Formatting
			'`\[b\](.+?)\[/b\]`is',
        	'`\[i\](.+?)\[/i\]`is',
        	'`\[u\](.+?)\[/u\]`is',
        	'`\[p\](.+?)\[/p\]`is',
        	'`\[p id=([a-zA-Z0-9]+)\](.+?)\[/p\]`is',

        	'`\[strike\](.+?)\[/strike\]`is',
        	'`\[h([0-9]+)\](.+?)\[/h([0-9]+)\]`is',
			'`\[ul\](.+?)\[/ul\]`is',
			'`\[li\](.+?)\[/li\]`is',
			'`\[li\](.+?)(\n)\[/li\]`is',
			'`\[list\](.+?)\[/list\]`is',
			'`\[\*\](.+?)(\n)`is',
			'`\[hr\]\[/hr\]`is',

        	//Colors
        	'`\[color=#([a-z0-9]{3,6})\](.+?)\[/color\]`is',

        	//Non-HREF links
			'`\[email\](.+?)\[/email\]`is',
        	'`\[img\](.+?)\[/img\]`is',
        	'`\[img class=(.+?)\](.+?)\[/img\]`is',
			'`\[img alt=(.+?)\](.+?)\[/img\]`is',

        	//HREF links
        	'`\[url=([a-z]+://)([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*?)?)\](.*?)\[/url\]`si',
        	'`\[url=([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*?)?)\](.*?)\[/url\]`si',
			'`\[url=\"([a-z]+://)([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*?)?)\"\](.*?)\[/url\]`si',
        	'`\[url\]([a-z]+?://){1}([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)\[/url\]`si',
        	'`\[url\]((www|ftp)\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*?)?)\[/url\]`si',
        	'`\[url=(.+?) title=(.+?)\](.+?)\[/url\]`si',
        	'`\[urn=(.+?) title=(.+?)\](.+?)\[/urn\]`si',
        	'`\[urn=(.+?)\](.+?)\[/urn\]`si',

        	//Embeded
			'`\[flash=([0-9]+),([0-9]+)\](.+?)\[/flash\]`is',
			'`\[youtube](.+?)\[/youtube\]`is',

        	'`\[quote\](.+?)\[/quote\]`is',
        	'`\[quote id=([0-9]+)\](.+?)\[/quote\]`is',

			'`\[code\](.+?)\[/code\]`is',
        	'`\[indent\](.+?)\[/indent\]`is',
        	'`\[size=([1-6]+)\](.+?)\[/size\]`is',
        	'`\[qu\](.+?)\[/qu\]`is',
        	'`\[qua\](.+?)\[/qua\]`is',
		);

		//these are what they get converted into
		$aReplaces =  array(
			//Formatting
			'<strong>\1</strong>',
	        '<em>\1</em>',
	        '<span style="border-bottom: 1px dotted">\1</span>',
	        '<p>\1</p>',
	        '<p id="\1">\2</p>',

	        '<strike>\1</strike>',
	        '<h\1>\2</h\3>',
			'<ul>\1</ul>',
			'<li>\1</li>',
			'<li>\1</li>',
			'<ul>\1</ul>',
			'<li>\1</li>',
			'<hr />',

	        //Colors
	        '<span style="color:#\1;">\2</span>',

	        //Non-HREF links
	        '<a href="mailto:\1">\1</a>',
	        '<img src="\1" alt="\1" />',
	        '<img src="\2" alt="\2" class="\1" />',
			'<img src="\1" alt="\2" />',

	        //HREF links
			'<a href="\1\2" rel="no-follow">\6</a>',
			'<a href="\1">\5</a>',
			'<a href="\1\2" rel="no-follow">\6</a>',
	        '<a href="\1\2" rel="no-follow">\1\2</a>',
	        '<a href="http://\1" rel="no-follow">\1</a>',
	        '<a href="\1" title="\2">\3</a>',
	        '<a rel="no-follow" href="\1" title="\2">\3</a>',
	        '<a ref="no-follow" href="\1">\1</a>',

	        //Embeded
			'<object width="\1" height="\2"><param name="movie" value="\3" /><embed src="\3" width="\1" height="\2"></embed></object>',
			'<object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/\1"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/\1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="425" height="344"></embed></object>',

	        '<strong>Quote:</strong><div class="quote">"<em>\1</em>"</div>',
	        '<strong>Quote:</strong><div class="quote">"<em>\2</em>"<a href="/forums/quoteid/\1">Goto Reply</a></div>',

			'<code>\1</code>',
	        '<pre>\1</pre>',
	        '<h\1>\2</h\1>',
	        '&#96;\1&#180;',
	        '&#8220;\1&#8221;',
		);

		//actually do the replacement
		if ($iLoop) {
			$cReturn = preg_replace($aPatterns, $aReplaces, $cText);
			$this->normalConvert($cReturn, ($iLoop - 1));
		} else {
			$cReturn = preg_replace($aPatterns, $aReplaces, $cText);
		}

		return $cReturn;
	}

	/**
	 * Validator::convertSmileys()
	 *
	 * @param string $cText
	 * @return string
	 */
	private function convertSmileys($cText) {
		//thse are the smileys to convert
		$aPatterns = array(
      		//General Smiley
            '`(:)([a-zA-Z0-9]+)(:)`is',

            //Literal Smileys
            '`\:\-\)|\:\)`mi',
            '`\:\(|\:\-\(`mi',
            '`\;\)|\;\-\)`mi',
            '`\:(\s+)\)`mi',
            '`\:(s)`mi',
            '`\:(ss)`mi',
            '`\:(p)`mi',
            '`\:(o)`mi',
        );

		//the smileys in the end
		$aReplaces = array(
            //General Smiley
            '<img src="/images/smiles/\2.png" alt="\2" />',

            //Literal Smiley
            '<img src="/images/smiles/smile.png" alt="smile" />',
            '<img src="/images/smiles/sad.png" alt="sad" />',
            '<img src="/images/smiles/wink.png" alt="wink" />',
            '<img src="/images/smiles/jawdrop.png" alt="jawdrop" />',
            '<img src="/images/smiles/barf.png" alt="barf" />',
            '<img src="/images/smiles/puzzled.png" alt="puzzled" />',
            '<img src="/images/smiles/tongue.png" alt="tongue" />',
            '<img src="/images/smiles/shock.png" alt="shock" />',
        );

		$cReturn = preg_replace($aPatterns, $aReplaces, $cText);

		return $cReturn;
	}

	/**
	 * Validator::convertOthers()
	 *
	 * @param string $cText
	 * @return string
	 */
	private function convertOthers($cText) {
		$aPatterns = array(
            //Stuff that doesnt translate properlly
            '`£`mi',
            '`‘`is',
            '`’`is',
            '`“`is',
            '`”`is',
            '`–`is',
            '`—`is',
            '`(\\?)`is',
            '`£`is',
            '`¤`is',
            '`¥`is',
            '`¿`is',
		);

		$aReplaces = array(
            //Stuff that doesnt translate properlly
            '&pound;',
            '&#145;',
            '&#146;',
            '&#147;',
            '&#148;',
            '&#150;',
            '&#151;',
            '&#63;',
            '&#163;',
            '&#164;',
            '&#165;',
            '&#191;',
		);

		$cReturn = preg_replace($aPatterns, $aReplaces, $cText);

		//remove accented
		$cReturn = str_replace("&Acirc;", "", $cReturn);

		return $cReturn;
	}

	/**
	 * Validator::convertLines()
	 *
	 * @param string $cText
	 * @return string

	 */
	private function convertLines($cText) {
		//now done that do the replacement of newlines
		$cText	= $this->mynl2br($cText);

		//Remove extra breaks
		$aPatterns = array(
    		'`<ul><br />`is',
    		'`</li><br />`is',
    		'`</ul><br />`is',
    	);

		$aReplaces = array(
    		'<ul>',
    		'</li>',
    		'</ul>',
    	);

		$cReturn = preg_replace($aPatterns, $aReplaces, $cText);

		return $cReturn;
	}

	/**
	 * Validator::bbCode()
	 *
	 * @param string $cText
	 * @param string $cIgnore Ignore these in the conversion
	 * @desc Parses the text for BBCode
	 * @return
	 */
	public function bbCode($cText, $cIgnore = false) {
		//do the normal conversion, then do the ignore functions
		$cReturn = $this->normalConvert($cText, 5);

		//now check the ignores
		switch ($cIgnore) {
			case "smileys":
				break;

			default:
				$cReturn = $this->convertSmileys($cReturn);
				break;
		}

		//language
		$cReturn	= $this->convertLanguage($cReturn);

		//now do the other conversions, like translations
		$cReturn = $this->convertOthers($cReturn);

		//now do the newlines and remove extras
		$cReturn = $this->convertLines($cReturn);

		return $cReturn;
	}

	/**
	 * Validator::convertLanguage()
	 *
	 * @param string $cText
	 * @return string
	 */
	private function convertLanguage($cText) {
		$aPattern	= array(
			'`Š`is',
			'`Ž`is',
			'`š`is',
			'`ž`is',
			'`Ÿ`is',
			'`À`is',
			'`Á`is',
			'`Â`is',
			'`Ã`is',
			'`Ä`is',
			'`Å`is',
			'`Æ`is',
			'`Ç`is',
			'`È`is',
			'`É`is',
			'`Ë`is',
			'`Ì`is',
			'`Í`is',
			'`Î`is',
			'`Ï`is',
			'`Ð`is',
			'`Ñ`is',
			'`Ò`is',
			'`Ó`is',
			'`Ô`is',
			'`Õ`is',
			'`Ö`is',
			'`×`is',
			'`Ø`is',
			'`Ù`is',
			'`Ú`is',
			'`Û`is',
			'`Ü`is',
			'`Ý`is',
			'`Þ`is',
			'`ß`is',
			'`à`is',
			'`á`is',
			'`â`is',
			'`ã`is',
			'`ä`is',
			'`å`is',
			'`æ`is',
			'`ç`is',
			'`è`is',
			'`é`is',
			'`ê`is',
			'`ë`is',
			'`ì`is',
			'`í`is',
			'`î`is',
			'`ï`is',
			'`ð`is',
			'`ñ`is',
			'`ò`is',
			'`ó`is',
			'`ô`is',
			'`õ`is',
			'`ö`is',
			'`ø`is',
			'`ù`is',
			'`ú`is',
			'`û`is',
			'`ü`is',
			'`ý`is',
			'`þ`is',
			'`ÿ`is',
		);

		$aReplace	= array(
			'&#138;',
			'&#142;',
			'&#154;',
			'&#158;',
			'&#159;',
			'&#192;',
			'&#193;',
			'&#194;',
			'&#195;',
			'&#196;',
			'&#197;',
			'&#198;',
			'&#199;',
			'&#200;',
			'&#201;',
			'&#202;',
			'&#203;',
			'&#204;',
			'&#205;',
			'&#206;',
			'&#207;',
			'&#208;',
			'&#209;',
			'&#210;',
			'&#211;',
			'&#212;',
			'&#213;',
			'&#214;',
			'&#215;',
			'&#216;',
			'&#217;',
			'&#218;',
			'&#219;',
			'&#219;',
			'&#220;',
			'&#221;',
			'&#222;',
			'&#223;',
			'&#224;',
			'&#225;',
			'&#226;',
			'&#227;',
			'&#228;',
			'&#229;',
			'&#230;',
			'&#231;',
			'&#232;',
			'&#233;',
			'&#234;',
			'&#235;',
			'&#236;',
			'&#237;',
			'&#238;',
			'&#239;',
			'&#240;',
			'&#241;',
			'&#242;',
			'&#243;',
			'&#244;',
			'&#245;',
			'&#246;',
			'&#248;',
			'&#249;',
			'&#250;',
			'&#251;',
			'&#252;',
			'&#253;',
			'&#254;',
			'&#255;',
		);

		$cReturn = preg_replace($aPattern, $aReplace, $cText);

		return $cReturn;
	}

	/**
	 * Validator::reverseOthers()
	 *
	 * @param string $cText
	 * @return string
	 */
	private function reverseOthers($cText) {
		$aPattern = array(
			'`&#63;`is',
			'`&#163;`is',
			'`&#164;`is',
			'`&#165;`is',
			'`&#191;`is',
		);

		$aReplace = array(
			'?',
			'£',
			'¤',
			'¥',
			'¿',
		);

		$cReturn = preg_replace($aPattern, $aReplace, $cText);

		return $cReturn;
	}

	/**
	 * Validator::reverseLanguage()
	 *
	 * @param string $cText
	 * @return string
	 */
	private function reverseLanguage($cText) {
		$aPattern = array(
			'`&#138;`is',
			'`&#142;`is',
			'`&#154;`is',
			'`&#158;`is',
			'`&#159;`is',
			'`&#192;`is',
			'`&#193;`is',
			'`&#194;`is',
			'`&#195;`is',
			'`&#196;`is',
			'`&#197;`is',
			'`&#198;`is',
			'`&#199;`is',
			'`&#200;`is',
			'`&#201;`is',
			'`&#202;`is',
			'`&#203;`is',
			'`&#204;`is',
			'`&#205;`is',
			'`&#206;`is',
			'`&#207;`is',
			'`&#208;`is',
			'`&#209;`is',
			'`&#210;`is',
			'`&#211;`is',
			'`&#212;`is',
			'`&#213;`is',
			'`&#214;`is',
			'`&#215;`is',
			'`&#216;`is',
			'`&#217;`is',
			'`&#218;`is',
			'`&#219;`is',
			'`&#219;`is',
			'`&#220;`is',
			'`&#221;`is',
			'`&#222;`is',
			'`&#223;`is',
			'`&#224;`is',
			'`&#225;`is',
			'`&#226;`is',
			'`&#227;`is',
			'`&#228;`is',
			'`&#229;`is',
			'`&#230;`is',
			'`&#231;`is',
			'`&#232;`is',
			'`&#233;`is',
			'`&#234;`is',
			'`&#235;`is',
			'`&#236;`is',
			'`&#237;`is',
			'`&#238;`is',
			'`&#239;`is',
			'`&#240;`is',
			'`&#241;`is',
			'`&#242;`is',
			'`&#243;`is',
			'`&#244;`is',
			'`&#245;`is',
			'`&#246;`is',
			'`&#248;`is',
			'`&#249;`is',
			'`&#250;`is',
			'`&#251;`is',
			'`&#252;`is',
			'`&#253;`is',
			'`&#254;`is',
			'`&#255;`is',
		);

		$aReplace	= array(
			'Š',
			'Ž',
			'š',
			'ž',
			'Ÿ',
			'À',
			'Á',
			'Â',
			'Ã',
			'Ä',
			'Å',
			'Æ',
			'Ç',
			'È',
			'É',
			'Ë',
			'Ì',
			'Í',
			'Î',
			'Ï',
			'Ð',
			'Ñ',
			'Ò',
			'Ó',
			'Ô',
			'Õ',
			'Ö',
			'×',
			'Ø',
			'Ù',
			'Ú',
			'Û',
			'Ü',
			'Ý',
			'Þ',
			'ß',
			'à',
			'á',
			'â',
			'ã',
			'ä',
			'å',
			'æ',
			'ç',
			'è',
			'é',
			'ê',
			'ë',
			'ì',
			'í',
			'î',
			'ï',
			'ð',
			'ñ',
			'ò',
			'ó',
			'ô',
			'õ',
			'ö',
			'ø',
			'ù',
			'ú',
			'û',
			'ü',
			'ý',
			'þ',
			'ÿ',
		);

		$cReturn = preg_replace($aPattern, $aReplace, $cText);

		return $cReturn;
	}

	/**
	 * Validator::textArea()
	 *

	 * @param string $cText
	 * @param string $cIgnore Send this to ignore the conversion stated
	 * @return string
	 */
	public function textArea($cText, $cIgnore = false) {
		if (!isset($cText[1])) { return false; }

		$cText = $this->bbCode($cText, $cIgnore);

		return $cText;
	}

	/**
	 * Validator::stripBBCode()
	 *
	 * @param string $cText
	 * @return string
	 */
	public function stripBBCode($cText) {
		if (!isset($cText[1])) { return false; }

		$aPatterns = array(
			//Formatting
			'`\[b\](.+?)\[/b\]`is',
        	'`\[i\](.+?)\[/i\]`is',
        	'`\[u\](.+?)\[/u\]`is',
        	'`\[strike\](.+?)\[/strike\]`is',
        	'`\[h([0-9]+)\](.+?)\[/h([0-9]+)\]`is',
			'`\[ul\](.+?)\[/ul\]`is',
			'`\[li\](.+?)\[/li\]`is',
			'`\[\*\](.+?)(\n)`is',
			'`\[hr\]\[/hr\]`is',

        	//Colors
        	'`\[color=#([a-z0-9]{3,6})\](.+?)\[/color\]`is',

        	//Non-HREF links
			'`\[email\](.+?)\[/email\]`is',
        	'`\[img\](.+?)\[/img\]`is',
        	'`\[img class=(.+?)\](.+?)\[/img\]`is',
			'`\[img alt=(.+?)\](.+?)\[/img\]`is',

        	//HREF links
        	'`\[url=([a-z]+://)([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*?)?)\](.*?)\[/url\]`si',
        	'`\[url=([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*?)?)\](.*?)\[/url\]`si',
			'`\[url=\"([a-z]+://)([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*?)?)\"\](.*?)\[/url\]`si',
        	'`\[url\]([a-z]+?://){1}([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)\[/url\]`si',
        	'`\[url\]((www|ftp)\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*?)?)\[/url\]`si',
        	'`\[url=(.+?) title=(.+?)\](.+?)\[/url\]`si',
        	'`\[urn=(.+?) title=(.+?)\](.+?)\[/urn\]`si',

        	//Embeded
			'`\[flash=([0-9]+),([0-9]+)\](.+?)\[/flash\]`is',
			'`\[youtube](.+?)\[/youtube\]`is',
        	'`\[quote\](.+?)\[/quote\]`is',
        	'`\[code\](.+?)\[/code\]`is',
        	'`\[indent\](.+?)\[/indent\]`is',
        	'`\[size=([1-6]+)\](.+?)\[/size\]`is',
        	'`\[qu\](.+?)\[/qu\]`is',
        	'`\[qua\](.+?)\[/qua\]`is',
		);

		//these are what they get converted into
		$aReplaces =  array(
			//Formatting
			'\1',
	        '\1',
	        '\1',
	        '\1',
	        '\2',
			'\1',
			'\1',
			'\2',
			'',


	        //Colors
	        '\2',

	        //Non-HREF links
	        '\1',
	        '\1',
	        '\2',
			'\2',

	        //HREF links
			'\1\2',
			'\1',
			'\1\2',
	        '\1\2',
	        'http://\1',

	        '\1',
	        '\1',

	        //Embeded
			'',
			'',
	        '\1',
	        //'<strong>Code:</strong><div class="quote"><code>\1</code></div>', //no idea why it had a quote div around it
	        '\1',
	        '\1',
	        '\2',
	        '\1',
	        '\1',
		);

		$cReturn = preg_replace($aPatterns, $aReplaces, $cText);

		return $cReturn;
	}

	/**
	* Validator::reverseText()
	*
	* @desc Make it easier to remember
	* @return string
	*/
	public function reverseText($cText, $cIgnore = false) {
		return $this->reverseTextArea($cText, $cIgnore);
	}

	/**
	 * Validator::reverseTextArea()
	 *
	 * @desc Reverse parses the test, turning HTML back into BBCode
	 * @param string $cText
	 * @param string $cIgnore, thse are ignored
	 * @return string
	 */
	public function reverseTextArea($cText, $cIgnore = false) {
		if (!isset($cText[1])) { return false; }

		//reverse others
		$cReturn	= $this->reverseLanguage($cText);
		$cReturn	= $this->reverseOthers($cReturn);

		$aSearch	= array(
			//urls of various types
			'`(<a rel="no-follow" href="(.+?)" title="(.+?)">)(.+?)(<\/a>)`si',
			'`(<a href="(.+?)" rel="no-follow">)(.+?)(<\/a>)`si',
            '`(<a href="(.+?)" title="(.+?)">)(.+?)(<\/a>)`is',
			'`(<a href="(.+?)">)(.+?)(<\/a>)`is',

			//images
			'`(<img src="(.+?)" \/>)`is',
			'`(<img src="(.+?)" alt="(.+?)" \/>)`is',

			//lits
			'`(<ul>)(.+?)(<\/ul>)`is',
			'`(<li>)(.+?)(<\/li>)`is',

			//newlines
			'`<br />`is',
			'`<hr />`is',

			//headings
			'`(<h([0-6])>)(.+?)(<\/h([0-6])>)`is',

			//word crap
			'`&#96;(.+?)&#180;`is',
			'`&#8220;(.+?)&#8221;`is',

			//youtube
			'`(<object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/(.+?)"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/(.+?)" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="425" height="344"></embed></object>)`is',

			//formatting
			'`(<em>)(.+?)(<\/em>)`is',
			'`(<span style="border-bottom: 1px dotted">)(.+?)(<\/span>)`is',
			'`(<div class="quote">)(.+?)(<\/div>)`is',
			'`(<code>)(.+?)(<\/code>)`is',
			'`(<strong>)(.+?)(<\/strong>)`is',
			'`(<pre>)(.+?)(<\/pre>)`is',
			'`(<span style="color:([a-zA-Z0-9\#]+);">)(.+?)(<\/span>)`is',
			'`(<p>)(.+?)(<\/p>)`is',
			'`(<p id="([a-zA-Z0-9]+)">)(.+?)(<\/p>)`is',
        );

		$aReplace	= array(
			//urls of various types
			'[urn=\2 title=\3]\4[/urn]',
			'[urn=\2]\3[/urn]',
            '[url=\2 title=\3]\4[/url]',
			'[url=\2]\3[/url]',

			//images
			'[img]\2[/img]',
			'[img alt=\3]\2[/img]',

			//lists
			'[list]' . "\n" . '\2[/list]' . "\n", //again needs newline
			'[*]\2' . "\n", //nees to be  a newline

			//newlines
			"\n",
			'[hr][/hr]',

			//headings
			'[h\2]\3[/h\5]',

			//word crap
			'[qu]\1[/qu]',
			'[qua]\1[/qua]',

			//youtube
			'[youtube]\2[/youtube]',

			//formatting
			'[i]\2[/i]',
			'[u]\2[/u]',
			'[quote]\2[/quote]',
			'[code]\2[/code]',
			'[b]\2[/b]',
			'[i]\2[/i]',
			'[color=\2]\3[/color]',
			'[p]\2[/p]',
			'[p id=\2]\3[/p]',
        );

		//smileys
		$aSearch_b = array(
			'`(<img src="\/images\/smiles\/)([a-zA-Z]+)(\.png" alt=")([a-zA-Z]+)(" \/>)`is',
		);

		$aReplace_b = array(
			':\4:',
		);

		$cReturn	= preg_replace($aSearch, $aReplace, $cReturn);

		//Set these ignores if in use
		if ($cIgnore) {
			switch($cIgnore){
				case "smileys":
					break;
			} // switch
		} else {
			$cReturn = preg_replace($aSearch_b, $aReplace_b, $cReturn);
		}

		return $cReturn;
	}

	/**
	 * Validator::mynl2br()
	 *
	 * @param string $cText
	 * @return string
	 */
	private function mynl2br($cText) {
		$aReplace = array(
			"\r\n"	=> '<br />',
			"\r"	=> '<br />',
			"\n"	=> '<br />',
			"\s"	=> '&nbsp;',
			"\t"	=> '&nbsp;&nbsp;&nbsp;&nbsp;',
		);

		return strtr($cText, $aReplace);
	}

	/**
	 * Validator::textInput()
	 *
	 * @desc Makes sure that teh code hasnt got any invalid chars in it
	 * @param string $cInput
	 * @return string
	 */
	public function textInput($cInput) {
		$cReturn	= false;

		//It doesnt have anything
		if (!isset($cInput[0])) { return false; }

		//Use filter var
		if (function_exists("filter_var")) {
			$aFilters	= array(FILTER_FLAG_ENCODE_HIGH, FILTER_FLAG_ENCODE_LOW, FILTER_FLAG_ENCODE_AMP);
			$cReturn	= filter_var($cInput, FILTER_SANITIZE_STRING, $aFilters);
		} else {
			if (preg_match("`([a-zA-Z0-9\-_]+)`is", $cInput)) {
				$cReturn = addslashes($cInput);
			}
		}

		return $cReturn;
	}

	/**
	 * Validator::numberInput()
	 *
	 * @desc Makes sure that only numbers are used, but because its from a form it isnt an int
	 * @param string $cInput
	 * @return int
	 */
	public function numberInput($cInput) {
		$cReturn	= false;

		//its not actually got any chars
		if (!isset($cInput[0])) { return false; }

		if (function_exists("filter_var")) {
			if (filter_var($cInput, FILTER_VALIDATE_INT)) {
				$cReturn	= filter_var($cInput, FILTER_SANITIZE_NUMBER_INT);
			}
		} else {
			if (preg_match("`([0-9\s]+)`is", $cInput)) {
				$cReturn = addslashes($cInput);
			}
		}

		return $cReturn;
	}

	/**
	 * Validator::fileUpload()
	 *
	 * @desc Makes sure its a valid upload
	 * @param string $cFileName
	 * @param bool $bType
	 * @return bool
	 */
	public function fileUpload($cFileName, $bType = false) {
		$bReturn		= false;
		$aConfImages	= false;
		$aConfFiles		= false;

		//Get the position, if there isnt one, then its not going to be valid
		$iDotPos = strrpos($cFileName, '.');
		if (!$iDotPos) { return false; }

		$aFilters		= $this->oNails->getConfig("fiters");
		if ($aFilters) {
			$aConfImages	= $aFilters['images'];
			$aConfFiles		= $aFilters['files'];
		}

		//Since they want to specify the image filters
		if ($aConfImages) {
			$aImages	= $aConfImages;
		} else {
			$aImages    = array("jpg", "png", "gif", "psd", "tiff");
		}

		//Since they want to specify the file filters
		if ($aConfFiles) {
			$aFiles	= $aConfFiles;
		} else {
			$aFiles     = array("txt", "zip", "rar", "doc", "docx", "pdf");
		}

		//What is its extension, true this isnt a very good check, becasue you could just name anything this
		$cExt = strtolower(substr($cFileName, ($iDotPost + 1)));

		switch($bType){
			case 1: //Files
				if (in_array($cExt, $aFiles)) {
					$bReturn = true;
				}
				break;
			case 2: //Images
				if (in_array($cExt, $aImages)) {
					$bReturn = true;
				}
				break;
			default:
				$aNewArray = array_merge($aImages, $aFiles);
				if (in_array($cExt, $aNewArray)) {
					$bReturn = true;
				}
				break;
		} // switch

		return $bReturn;
	}

	/**
	 * Validator::validateCaptcha()
	 *
	 * @return bool
	 */
	public function validateCaptcha() {
		if ($_SESSION['captcha'] == $_POST['captcha']) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Validator::uploadFile()
	 *
	 * @param array $aFile
	 * @param int $iUserID
	 * @param string $cDirectory
	 * @param bool $bTimeStamp
	 * @return string
	 */
	public function uploadFile($aFile, $iUserID, $cDirectory, $bTimeStamp = false) {
		$cError = false;

		if ($bTimeStamp) {
			$cFile = SITEPATH . "/" . $cDirectory . "/" . time() . $aFile['name'];
		} else {
			$cFile = SITEPATH . "/" . $cDirectory . "/" . $aFile['name'];
		}

		if (move_uploaded_file($aFile['tmpName'], $cFile)) {
			$aInsert = array($iUserID, $aFile['name'], strtolower($aFile['ext']));
			$oDB->write("INSERT INTO files (iUserID, tsDated, cFile, cType) VALUES ('?', UNIX_TIMESTAMP(), '?', '?')", $aInsert);
		} else {
			$cError = "The file didnt upload<br />";
			$cError .= $aFile['error'] . "<br />";
		}

		return $cError;
	}

	/**

	 * Validator::validateDate()
	 *
	 * @param date $dDate
	 * @return string
	 */
	public function validateDate($dDate) {
		if (strstr($dDate, ":")) {
			$aDate	= explode("/", $dDate);

			$iSpace = strpos($aDate[2], " ");

			$cYear	= substr($aDate[2], 0, $iSpace);
			$cTime	= substr($aDate[2], ($iSpace + 1));

			$aTime	= explode(":", $cTime);

			return mktime($aTime[0], $aTime[1], 0, $aDate[1], $aDate[0], $cYear);
		} else {
			$aDate = explode("/",$dDate);
			return mktime(0, 0, 0, $aDate[1], $aDate[0], $aDate[2]);
		}
	}

	/**
	 * Validator::__destruct()
	 *
	 */
	public function __destruct() {
		$this->oForm	= false;
		$this->oNails	= false;
	}
}
