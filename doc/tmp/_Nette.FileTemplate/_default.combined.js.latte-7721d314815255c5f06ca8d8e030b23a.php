<?php //netteCache[01]000416a:2:{s:4:"time";s:21:"0.66066900 1333123706";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:19:"Nette\Caching\Cache";i:1;s:9:"checkFile";}i:1;s:96:"C:\Program Files\WaterProof\PHPEdit\4.0.0.11350\Tools\ApiGen\templates\default\combined.js.latte";i:2;i:1332496707;}i:1;a:3:{i:0;a:2:{i:0;s:19:"Nette\Caching\Cache";i:1;s:10:"checkConst";}i:1;s:25:"Nette\Framework::REVISION";i:2;s:28:"$WCREV$ released on $WCDATE$";}}}?><?php

// source file: C:\Program Files\WaterProof\PHPEdit\4.0.0.11350\Tools\ApiGen\templates\default\combined.js.latte

?><?php
// prolog Nette\Latte\Macros\CoreMacros
list($_l, $_g) = Nette\Latte\Macros\CoreMacros::initRuntime($template, 'q49mitw3s1')
;
// prolog Nette\Latte\Macros\UIMacros

// snippets support
if (!empty($_control->snippetMode)) {
	return Nette\Latte\Macros\UIMacros::renderSnippets($_control, $_l, get_defined_vars());
}

//
// main template
// ?>

var ApiGen = ApiGen || {};
ApiGen.config = <?php echo Nette\Templating\Helpers::escapeJs($config->template) ?>;

<?php $scripts = array('jquery.min.js', 'jquery.cookie.js', 'jquery.sprintf.js', 'jquery.autocomplete.js', 'jquery.sortElements.js', 'main.js') ;$dir = dirname($template->getFile()) ?>

<?php $iterations = 0; foreach ($scripts as $script): echo file_get_contents("$dir/js/$script") ?>

<?php $iterations++; endforeach ;