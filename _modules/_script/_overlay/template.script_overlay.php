<? function script_overlay($val)
{
	if (testValue('ajax')) return;
	m('script:jq');
?>
<script src="script/jQuery.overlay.js"></script>
<? } ?>
