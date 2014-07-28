<? function script_maskInput($val){ 
	m('script:jq');
	m('scriptLoad', "script/jquery.maskedinput.min.js");
?>
<script>
$(function(){
	$(document).on("jqReady ready", function()
	{
		$("input.phone").mask("+7(999) 999-99-99");
	});
});
</script>
<? } ?>
