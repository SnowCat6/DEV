<? function script_jq_ui($val)
{
	module('script:jq');
	$ini	= getCacheValue('ini');
	$uiTheme= @$ini[':']['jQueryUI'];
	
	$jQuery	= getCacheValue('jQuery');
	$ver	= $jQuery['jQueryUIVersion'];
	if (!$uiTheme) $uiTheme=$jQuery['jQueryUIVersionTheme'];
	m('page:style', "script/$ver/css/$uiTheme/$ver.min.css");
?>
<? if (testValue('ajax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
if (typeof jQuery.ui == 'undefined')
	loadScriptFile("<?= globalRootURL?>/script/<?= $ver?>/js/<?= $ver?>.min.js");
 /*]]>*/
</script>
<? return; } ?>
<? m('scriptLoad', "script/$ver/js/$ver.min.js") ?>
<? } ?>
