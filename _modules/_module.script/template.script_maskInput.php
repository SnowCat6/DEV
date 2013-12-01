<? function script_maskInput($val){ module('script:jq')?>
<script type="text/javascript" src="<?= globalRootURL?>/script/jquery.maskedinput.min.js"></script>
<script>
$(function(){
	$("input.phone").mask("+7(999) 999-99-99");
});
</script>
<? } ?>
