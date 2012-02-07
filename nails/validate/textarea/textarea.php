<?php
/**
 * Validate_TextArea
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Validate_TextArea {
	//Traits
	use Text;

	public $mValue;
	private $mPreValue;

	/**
	 * Validate_Text::__construct()
	 *
	 */
	function __construct() {

	}

	/**
	 * Validate_TextArea::validate()
	 *
	 * @param mixed $mEntry
	 * @return mixed
	 */
	public function validate($mEntry) {
		$this->mPrevalueValue	= $mEntry;
		$this->mValue			= $this->doValidate();

		return $this;
	}

	/**
	 * Validate_TextArea::doValidate()
	 *
	 * @return string
	 */
	private function doValidate() {
		//do the normal conversion, then do the ignore functions
		$cReturn = $this->normalConvert($this->mPreValue, 5);

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
	 * Validate_TextArea::normalConvert()
	 *
	 * @param string $cText
	 * @param int $iLoop
	 * @return string
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
			'<iframe width="560" height="349" src="http://www.youtube.com/embed/\3?theme=light&color=red" frameborder="0" allowfullscreen></iframe>',

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
	 * Validate_TextArea::convertSmileys()
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
	 * Validate_TextArea::convertLanguage()
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
	 * Validate_TextArea::convertOthers()
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
	 * Validate_TextArea::convertLines()
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
}