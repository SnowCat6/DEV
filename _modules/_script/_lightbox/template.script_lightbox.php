﻿<? function script_lightbox($val)
{
	m('script:jq');
	m('page:style', 'css/lightbox.css');
?>
<? if (testValue('ajax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
$(function(){
	if (typeof lightbox == 'undefined'){
		$.getScript('<?= globalRootURL?>/script/lightbox.js');
	}
});
 /*]]>*/
</script>
<? return; } ?>
<? m('scriptLoad', "script/lightbox.js") ?>
<? } ?>
