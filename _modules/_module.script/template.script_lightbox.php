<? function script_lightbox($val){ module('script:jq'); ?>
<link rel="stylesheet" type="text/css" href="<?= globalRootURL?>/script/lightbox2.51/css/lightbox.css"/>
<? if (testValue('ajax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
$(function(){
	if (typeof lightbox == 'undefined'){
		$.getScript('<?= globalRootURL?>/script/lightbox2.51/js/lightbox.js');
	}
});
 /*]]>*/
</script>
<? return; } ?>
<script type="text/javascript" src="<?= globalRootURL?>/script/lightbox2.51/js/lightbox.js"></script>
<? } ?>
