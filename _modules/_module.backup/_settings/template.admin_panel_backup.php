<?
function admin_panel_backup(&$data)
{
	if (!access('write', 'backup')) return;
	module('script:ajaxLink');
	$note = "Плановая архивация\r\n";
?>
<a href="<?= getURL('backup_all')?>" id="ajax">Резервные копии</a>
<p>
<form action="<?= getURL('backup_now')?>" method="post" class="ajaxForm">
Ваш комментарий к резервной копии
<div><textarea name="backupNote" rows="5" class="input w100">{$note}</textarea></div>
<p><input name="backupPassword" type="password" class="input password" size="16" /> 
Защитить паролем, восстановление и удаление возможно только при вводе пароля</p>
<p><input type="checkbox" name="backupImages" id="backupImages" /> <label for="backupImages">Хранить изображения (дополнительное место на диске)</label></p>
<p><input type="submit" value="Создать резервную копию" class="ui-button ui-widget ui-state-default ui-corner-all" /></p>
</form>
</p><? return '200-Резервные копии'; } ?>