<?
function admin_panel_backup(&$data)
{
	$note = "Плановая архивация\r\n";
?>
<a href="<?= getURL('backup_all')?>" id="ajax">Резервные копии</a>
<p>
<form action="<?= getURL('backup_now')?>" method="post" class="ajaxForm">
Ваш комментарий к резервной копии
<div><textarea name="backupNote" rows="5" class="input w100">{$note}</textarea></div>
<div><input type="checkbox" name="backupImages" id="backupImages" /> <label for="backupImages">Хранить изображения (дополнительное место на диске)</label></div>
<p><input type="submit" value="Создать резервную копию" class="ui-button ui-widget ui-state-default ui-corner-all" /></p>
</form>
</p><? return 'Резервные копии'; } ?>