<? function admin_SQLquery(&$val)
{
	if (!hasAccessRole('developer')) return;
//	setTemplate('');
	$LogSQLquery	= getValue('LogSQLquery');
?>
{{page:title=Выполнить код SQL}}
{{script:ajaxForm}}
<form action="{{url:admin_SQLquery}}" method="POST" class="ajaxForm ajaxReload">
<h2 style="margin:0">SQL запрос: <input type="submit" value="RUN" class="button" /></h2>
<textarea name="LogSQLquery" class="input w100" rows="5" style="font-size:11px">{$LogSQLquery}</textarea>
<? if ($LogSQLquery){ ?>
<?
$db		= new dbRow();
$time	= getmicrotime();
$db->exec($LogSQLquery);
$time	= round(getmicrotime() - $time, 5);
$error	= $db->error();
?>
<h2>Результат: {$time} сек.</h2>
<? messageBox($error) ?>
<?
$header	= false;
while($data = $db->dbResult()){ ?>
<? if (!$header){ $header=true; ?>
<table class="table" cellpadding="0" cellspacing="0">
<tr>
<? foreach($data as $name=>$val){ ?>
<th>{$name}</th>
<? } ?>
</tr>
<? } ?>
<tr>
<? foreach($data as $name=>$val){ ?>
<td valign="top">{$val}</td>
<? } ?>
</tr>
<? } ?>
<? if ($header){ ?>
</table>
<? } ?>
<? } ?>
</form>
<? } ?>
