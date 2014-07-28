<? function admin_SQLquery(&$val)
{
	if (!hasAccessRole('developer')) return;
	$LogSQLquery	= getValue('LogSQLquery');
?>

<?
$db		= new dbRow();
if ($LogSQLquery){
	$time	= getmicrotime();
	$db->exec($LogSQLquery);
	$time	= round(getmicrotime() - $time, 5);
	$error	= $db->error();
}else{
	$time	= '-';
}
?>

{{page:title=Выполнить код SQL}}
{{script:ajaxForm}}
{{ajax:template=ajax_edit}}
{{script:jq_ui}}
<form action="{{url:admin_SQLquery}}" method="POST" class="ajaxForm ajaxReload">
<div id="SQLtabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-state-default ui-corner-top"><a href="#SQLquery">SQL запрос</a></li>
    <li class="ui-state-default ui-corner-top"><a href="#SQLresult">Результат  {$time} сек.</a></li>
    <li class="ui-state-default ui-corner-top"><a href="{{url:admin_SQLqueryTables}}">Статистика базы</a></li>
	<li style="float:right"><input name="docSave" type="submit" value="Выполнить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="SQLquery">
<textarea name="LogSQLquery" class="input w100" rows="15">{$LogSQLquery}</textarea>
</div>

<div id="SQLresult">
<? messageBox($error) ?>
<? showSQLtable($db) ?>
</div>

</div>
</form>
<script>
$(function(){
	$("#SQLtabs").tabs();
});
</script>
<? } ?>
<?
//	+function admin_SQLqueryTables
function admin_SQLqueryTables(&$val){
	setTemplate('ajaxResult');
	$db		= new dbRow();
	$dbName	= $db->dbName();
	$prefix	= $db->dbTablePrefix();
	
	$db->exec("SHOW TABLE STATUS FROM `$dbName` WHERE `Name` LIKE '$prefix%'");
	showSQLtable($db);
}?>
<? function showSQLtable(&$db){ ?>
<div style="overflow:auto">
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
</div>
<? } ?>