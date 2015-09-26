<? function script_file_delete(&$val){
	m('script:jq_ui');
	m('script:ajaxLink');
?>
<script>
$(function(){
	$("a[href*='file_images_delete/']")
	.unbind('click')
	.click(function()
	{
		var url = $(this).attr("href") + 'Yes';
		$("#dialog-confirm").remove();
		$("#file-dialog-confirm" ).dialog({
			resizable: false, height:200, width: 600, modal: true,
			buttons: {
				"Удалить": function() {
					$(this).dialog("close");
					ajaxLoad(url, 'ajax');
				},
			Cancel: function() {
					$(this).dialog("close");
				}
			}
		});
  		return false;
	});
});
</script>
<div id="file-dialog-confirm" title="Удалить файл?" style="display:none">
  <p>
      <span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>
      Вы желаете удалить файл, это действие отменить нельзя.
  </p> Вы уверены?
</div>
<? } ?>