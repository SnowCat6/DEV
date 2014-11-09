<? function script_jq_ui($val)
{
	module('script:jq');
	$ini	= getCacheValue('ini');
	$uiTheme= $ini[':']['jQueryUI'];
	
	$jQuery	= getCacheValue('jQuery');
	$ver	= $jQuery['jQueryUIVersion'];
	if (!$uiTheme) $uiTheme=$jQuery['jQueryUIVersionTheme'];
	
	m('page:style', "script/$ver/jquery-ui.structure.min.css");
	m('page:style', "script/$ver/themes/$uiTheme/jquery-ui.min.css");
	m('page:style', "script/$ver/themes/$uiTheme/theme.css");
?>
<? if (testValue('ajax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
if (typeof jQuery.ui == 'undefined')
	loadScriptFile("<?= globalRootURL?>/script/{$ver}/jquery-ui.min.js");
 /*]]>*/
</script>
<? return; } ?>
<? m('scriptLoad', "script/$ver/jquery-ui.min.js") ?>
<? } ?>
