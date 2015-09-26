<? function import_import(&$val, &$data)
{
	$importFiles	= $_FILES['importFiles'];
	if (is_array($importFiles))
	{
		foreach($importFiles['name'] as $ix => $name)
		{
			moduleEx('translit', $name);
			
			$synch	= array($name => $name);
			event('import.delete', $synch);
			
			$path	= importFolder . "/$name";
			if (move_uploaded_file($importFiles['tmp_name'][$ix], $path)){
				event('import.synch', 	$synch);
			}
		}
	}

	$delete	= getValue('doDeleteFile');
	if (is_array($delete) && $delete){
		$files	= getFiles(importFolder, '');
		foreach($files as $name=>$path){
			if ($delete[$name]) unlink($path);
		}
	}

	$delete	= getValue('doDelete');
	if (is_array($delete) && $delete){
		foreach($delete as $name => &$val) $val = $name;
		event('import.delete', $delete);
	}

	$cancel	= getValue('doCancel');
	if (is_array($cancel) && $cancel){
		foreach($cancel as $name => &$val) $val = $name;
		event('import.cancel', $cancel);
	}

	$cancel	= getValue('doSynchRepeat');
	if (is_array($cancel) && $cancel){
		foreach($cancel as $name => &$val) $val = $name;
		event('import.cancel',	$cancel);
		event('import.synch', 	$cancel);
	}

	$synch	= getValue('doSynch');
	if (is_array($synch) && $synch){
		foreach($synch as $name => &$val) $val = $name;
		event('import.synch', $synch);
	}
	
	m('script:import');
?>
{{ajax:template=ajaxResult}}
<div id="importFiles">
<form action="{{url:import}}" method="post" enctype="multipart/form-data" id="reload">
<div><? importInfo() ?></div>
<p>
	<input type="submit" class="button" title="Загрузить файлы" value="Импорт" />
    <input type="file" name="importFiles[]" multiple>
</p>
</form>
</div>
<? } ?>

<? function importInfo($bDoSynch = false)
{
	$locks	= array();
	event('import.source', $locks);
	foreach($locks as $name => &$s){
		if (!$s->lockTimeout()) continue;
		$bDoSynch = false;
	}
	if ($bDoSynch){
		foreach($locks as $name => &$s){
			$status		= $s->getValue('status');
			if ($status == 'complete') continue;
			if ($s->lockTimeout()) break;

			$s2	= array($name => $name);
			event('import.synch', $s2);
			break;
		}
	}
	m('script:ajaxLink');
	$files	= getFiles(importFolder, '');
?>
<link rel="stylesheet" type="text/css" href="css/jqImportCommit.css">
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="table">
  <tr>
    <th>Удалить</th>
    <th>Источник</th>
    <th>Статус</th>
    <th>&nbsp;</th>
    <th>Процесс</th>
    <th width="100%">Статистика</th>
    <th>Действия</th>
  </tr>
<? foreach($locks as $name => &$synch)
{
	$files[$name]	= ''; unset($files[$name]);
	$comment	= $synch->getValue('comment');
	
	$status		= $synch->getValue('status');
	if (!$status) $status = '---';
	
	$progress	= $synch->getValue('progress');
	
	$lockInfo	= '';
	$timeout	= $synch->lockTimeout();
	if ($timeout)
	{
		$info		= $synch->info();
		$maxLock	= $synch->lockMaxTimeout();
		$userIP		= GetStringIP($info['userIP']);
		$userID		= $info['userID'];
		
		$userData	= user::get($userID);
		$userName	= m('user:name', $userData);

		$lockInfo .= "<div>Locked: $timeout/$maxLock сек.</div>";
		$lockInfo .= "<div>UserIP: $userIP, UserID: $userID $userName</div>";
		
		$lockInfo	= "<a class=\"lockInfo\" href=\"#\">Lock<div class=\"info\">$lockInfo</div></a>";
	}
?>
  <tr>
    <td>
		<input type="submit" class="button" name="doDelete[{$name}]" value="x" />
    </td>
    <td nowrap="nowrap" title="{$info[userInfo]}">
    <div>{$name}</div>
    {!$comment}
    </td>
    <td nowrap="nowrap">
    <div>{$status}</div>
<? if ($synch->logRead(1)){ ?>
    <a href="{{url:import_log=name:$name}}" id="ajax">лог импорта</a>
<? } ?>
</td>
    <td nowrap="nowrap">{!$progress}</td>
    <td nowrap="nowrap">{!$lockInfo}</td>
    <td nowrap="nowrap"><?
$statistic	= $synch->getValue('statistic');
if (!$statistic) $statistic = array();

	echo'<div>',
		'Каталоги: ',
		'pass ',	(int)$statistic['catalog']['pass'], ', ',
		'add: ',	(int)$statistic['catalog']['add'], ', ',
		'update: ',	(int)$statistic['catalog']['update'], ', ',
		'error: ',	(int)$statistic['catalog']['error'],
		'</div>';
	
	echo'<div>',
		'Товары: ',
		'pass ',	(int)$statistic['product']['pass'], ', ',
		'add: ',	(int)$statistic['product']['add'], ', ',
		'update: ',	(int)$statistic['product']['update'], ', ',
		'error: ',	(int)$statistic['product']['error'],
		'</div>';

    ?></td>
    <td nowrap="nowrap">
<? switch($status){ ?>
<? case '---': ?>
  <div><input type="submit" class="button w100" name="doSynch[{$name}]" value="Начать" /></div>
  <? break; ?>
<? case 'complete': ?>
  <div><input type="submit" class="button w100" name="doSynchRepeat[{$name}]" value="Повторить" /></div>
  <? break; ?>
  <? default: ?>
  <div>
  <input type="submit" class="button" name="doSynch[{$name}]" value="Продолжить" />
  <input type="submit" class="button" name="doCancel[{$name}]" value="||" />
  </div>
  <? break; ?>
<? } ?>
</td>
  </tr>
<? } ?>
<? if ($files){ ?>
  <tr>
    <th>&nbsp;</th>
    <th>Название</th>
    <th colspan="4">Путь</th>
    <th>&nbsp;</th>
  </tr>
<? } ?>
<? foreach($files as $name=>$path){ ?>
  <tr>
    <td>
        <input type="submit" class="button" name="doDeleteFile[{$name}]" value="x" />
    </td>
    <td>{$name}</td>
    <td colspan="4">{$path}</td>
    <td>&nbsp;</td>
  </tr>
<? } ?>
</table>
<? } ?>
<? function script_import(&$val){ ?>
<script>
function importTimeout(){
	return;
	$("#reload > div").load($("#reload").attr("action") + "?ajax=result", function(){
		$(document).trigger("jqReady");
		setTimeout(importTimeout, 5*1000);
	}, function(){
		setTimeout(importTimeout, 5*1000);
	});
	$("#importFile").change(function(){
		$(this).parents("form").submit();
	});
}
$(importTimeout);
</script>
<? } ?>
