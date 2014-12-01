<?
function admin_panel_backup(&$data)
{
	if (!access('write', 'backup')) return;
	module('script:ajaxForm');
	module('script:ajaxLink');
	$note = "Плановая архивация\r\n";
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="admin">
  <tbody>
    <tr>
      <td width="50%" valign="top">
      
{{script:ajaxForm}}
<form action="<?= getURL('backup_now')?>" method="post" class="admin ajaxFormNow">
<h2 class="ui-state-default">Создать резервную копию сайта</h2><br>

Ваш комментарий к резервной копии
<div><textarea name="backupNote" rows="5" class="input w100">{$note}</textarea></div>

<p>
    <input type="checkbox" name="backupImages" id="backupImages" /> 
    <label for="backupImages">Сохранить файлы</label><br>
    <i>Сохраняет вс пользовательские файлы и изображения на сайте, при восстановлении сотрет все более новые файлы.</i>
</p>

<p>
    <input type="checkbox" name="backupInstall" id="backupInstall" /> 
    <label for="backupInstall">Создать файл установки сайта</label><br>
	<i>Создает архив для скачивания для установки сайта на новом месте. Работает только при установке пароля на архив.
    <a href="{{url}}install_restore.txt" target="new">Инстукция по установке.</a></i>
<? if (!extension_loaded("zip")){ ?>
	<div class="message error">Не установленно расширение ZIP, опция &quot;<strong>Создать файл установки сайта не</strong>&quot; не доступна.</div>
<? } ?>
</p>

<p>
    <input name="backupPassword" type="password" class="input password" size="50" placeholder="Введите пароль для восстановления сайта" /><br>
    <i>Защитить паролем, восстановление и удаление возможно только при вводе пароля</i>
</p>

<p>
<input type="submit" value="Создать резервную копию" class="ui-button ui-widget ui-state-default ui-corner-all" />
</p>
</form>
      
      </td>
      <td width="50%" valign="top" style="padding-left:50px">
<?
module('script:ajaxLink');
module('script:ajaxForm');
$backupFolder	= localRootPath.'/_backup';
$folders		= getDirs($backupFolder);
$count			= count($folders);
?>
<h2><a href="{{url:backup_all}}" id="ajax">Резервные копии</sup></a> <sup>{$count}</h2><br>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
    <tr>
      <td width="100%" valign="top">
<?
$ix		= 0;
$folders= array_reverse($folders);
foreach($folders as $name => $backupFolder)
{
	if (++$ix > 3) break;
	
	$url	= getURL("backup_$name");
	$time	= date('d.m.Y H:i:s', filemtime($backupFolder));
	$note	= file_get_contents("$backupFolder/note.txt");
	$images	= is_dir("$backupFolder/images")?' + изображения':'';
	if ($bHasPassword = is_file("$backupFolder/password.bin")){
		$images .= ' + пароль';
	}
?>
    <b><a href="{!$url}" id="ajax">{$name}</a></b> <i>{$time}</i>{$images}
    <pre><blockquote>{$note}</blockquote></pre>
<?	} ?>
      </td>
      <td valign="top" nowrap="nowrap">
<? foreach($folders as $name => $backupFolder)
{
	if (--$ix > 0) continue;
	$url	= getURL("backup_$name");
	$time	= date('d.m.Y H:i:s', filemtime($backupFolder));
?>
<div>
    <a href="{!$url}" id="ajax">{$name}</a>
</div>
<? } ?>
      </td>
    </tr>
  </tbody>
</table>

      </td>
    </tr>
  </tbody>
</table>


<? return '200-Резервные копии'; } ?>