<?php //netteCache[01]000417a:2:{s:4:"time";s:21:"0.04304400 1333123707";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:19:"Nette\Caching\Cache";i:1;s:9:"checkFile";}i:1;s:97:"C:\Program Files\WaterProof\PHPEdit\4.0.0.11350\Tools\ApiGen\templates\default\@elementlist.latte";i:2;i:1332496707;}i:1;a:3:{i:0;a:2:{i:0;s:19:"Nette\Caching\Cache";i:1;s:10:"checkConst";}i:1;s:25:"Nette\Framework::REVISION";i:2;s:28:"$WCREV$ released on $WCDATE$";}}}?><?php

// source file: C:\Program Files\WaterProof\PHPEdit\4.0.0.11350\Tools\ApiGen\templates\default\@elementlist.latte

?><?php
// prolog Nette\Latte\Macros\CoreMacros
list($_l, $_g) = Nette\Latte\Macros\CoreMacros::initRuntime($template, 'stikxxx01c')
;
// prolog Nette\Latte\Macros\UIMacros
//
// block elements
//
if (!function_exists($_l->blocks['elements'][] = '_lbe2e6ddacbe_elements')) { function _lbe2e6ddacbe_elements($_l, $_args) { extract($_args)
;$iterations = 0; foreach ($elements as $element): ?><tr>
	<td class="name"><a href="<?php echo htmlSpecialChars($template->elementUrl($element)) ?>
"<?php if ($_l->tmp = array_filter(array($element->deprecated ? 'deprecated':null, !$element->valid ? 'invalid':null))) echo ' class="' . htmlSpecialChars(implode(" ", array_unique($_l->tmp))) . '"' ?>
><?php if ($namespace): echo Nette\Templating\Helpers::escapeHtml($element->shortName, ENT_NOQUOTES) ;else: echo Nette\Templating\Helpers::escapeHtml($element->name, ENT_NOQUOTES) ;endif ?></a></td>
	<td><?php echo $template->shortDescription($element) ?></td>
</tr>
<?php $iterations++; endforeach ;
}}

//
// end of blocks
//

// template extending and snippets support

$_l->extends = empty($template->_extended) && isset($_control) && $_control instanceof Nette\Application\UI\Presenter ? $_control->findLayoutTemplateFile() : NULL; $template->_extended = $_extended = TRUE;


if ($_l->extends) {
	ob_start();

} elseif (!empty($_control->snippetMode)) {
	return Nette\Latte\Macros\UIMacros::renderSnippets($_control, $_l, get_defined_vars());
}

//
// main template
//
if ($_l->extends) { ob_end_clean(); return Nette\Latte\Macros\CoreMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render(); } ?>

<?php if ($classes): ?><table class="summary" id="classes">
<caption>Classes summary</caption>
<?php call_user_func(reset($_l->blocks['elements']), $_l, array('elements' => $classes) + get_defined_vars()) ?>
</table>
<?php endif ?>

<?php if ($interfaces): ?><table class="summary" id="interfaces">
<caption>Interfaces summary</caption>
<?php call_user_func(reset($_l->blocks['elements']), $_l, array('elements' => $interfaces) + get_defined_vars()) ?>
</table>
<?php endif ?>

<?php if ($traits): ?><table class="summary" id="traits">
<caption>Traits summary</caption>
<?php call_user_func(reset($_l->blocks['elements']), $_l, array('elements' => $traits) + get_defined_vars()) ?>
</table>
<?php endif ?>

<?php if ($exceptions): ?><table class="summary" id="exceptions">
<caption>Exceptions summary</caption>
<?php call_user_func(reset($_l->blocks['elements']), $_l, array('elements' => $exceptions) + get_defined_vars()) ?>
</table>
<?php endif ?>

<?php if ($constants): ?><table class="summary" id="constants">
<caption>Constants summary</caption>
<?php call_user_func(reset($_l->blocks['elements']), $_l, array('elements' => $constants) + get_defined_vars()) ?>
</table>
<?php endif ?>

<?php if ($functions): ?><table class="summary" id="functions">
<caption>Functions summary</caption>
<?php call_user_func(reset($_l->blocks['elements']), $_l, array('elements' => $functions) + get_defined_vars()) ?>
</table>
<?php endif ;