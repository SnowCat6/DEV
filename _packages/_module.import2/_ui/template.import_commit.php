<?
function import_commit(&$val)
{
	$import	= new importBulk();
	$db		= $import->db();
	$ddb	= module('doc');
	

	if (getValue('importDoSClear'))
	{
//		$table	= $db->table();
//		$db->exec("DELETE FROM $table");
		$import->clear();
	}
	
	$bDelete	= getValue('deleteValue')?1:0;
	$bIgnore	= getValue('ignoreValue')?1:0;
	$i			= getValue('import');
	if (is_array($i))
	{
		if (getValue('importDoDelete')){
			$db->delete($i);
		}else
		if (getValue('importDoIgnore')){
			$db->setValue($i, 'ignore', $bIgnore);
		}
		if (getValue('importDoMarkDelete')){
			$db->setValue($i, 'delete', $bDelete);
		}
	}
	if (getValue('importDoSynch')){
		importCommit::doCommit($val);
	}
	
	$acrion	= 'Обработать данные';
	$synch	= importCommit::getSynch();
	$synch->read();
	if ($val = $synch->lockTimeout())
	{
		$max	= $synch->lockMaxTimeout() - $val;
		$acrion	= "Продолжить через $max сек.";
	}else
	if ($val = $synch->getValue('status'))
	{
		$acrion	= "Статус $val";
	}
?>

{{script:importCommit}}
{{ajax:template=ajaxResult}}
{{script:ajaxForm}}
{{script:ajaxLink}}
{{script:preview}}

<form action="{{url:#}}" method="post" class="commitForm">

<div>
    <input type="submit" name="importDoSynch" value="{$acrion}" class="button">
    <input type="submit" name="importDoSClear" value="Очистить все" class="button" style="float:right">
</div>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="table" style="display:none">
  <tr>
    <th nowrap="nowrap">Выполнить со всеми</th>
    <th nowrap="nowrap">&nbsp;</th>
    <th nowrap="nowrap"></th>
    <th colspan="3" nowrap="nowrap">Только с отмеченными</th>
    </tr>
  <tr>
    <td><input type="submit" name="importDoSynch" value="Обработать данные" class="button"></td>
    <td><input type="submit" name="importDoSClear" value="Очистить все" class="button"></td>
    <td width="100%"></td>


    <td nowrap="nowrap">
        <input type="checkbox" name="deleteValue" {checked:$bDelete} />
        <input type="submit"  class="button" name="importDoMarkDelete" value="Пометить для удаления" />
    </td>
    <td nowrap="nowrap">
        <input type="checkbox" name="ignoreValue" {checked:$bIgnore} />
        <input type="submit"  class="button" name="importDoIgnore" value="Игнорировать">
    </td>
    <td><input type="submit" name="importDoDelete" value="Удалить" class="button"></td>

  </tr>
</table>

<?
	$key	= $db->key;
	$db->order	= "date ASC, $key ASC";
	$db->open('doc_id = 0');
	
	$p	= dbSeek($db, 100);
?>

{!$p}

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table importCommit">
<tr>
    <th colspan="2"><label>
<?php /*?>
    <input type="checkbox" class="importSelectAll" />
<?php */?>    Тип / отметка</label></th>
    <th width="100%">Наименование</th>
</tr>
<?
while($data = $db->next())
{
	$id	= $db->id();
	if ($data['doc_id']){
		$msg		= $data['updated']?'OK':'update';
		$url		= getURL($ddb->url($data['doc_id']));
		$document	= "<a href=\"$url\" id=\"ajax\" class=\"preview\">$msg</a>";
	}else{
		$document	= '<span class="new">new</span>';
	}
	$ignore	= $data['ignore']?'<div>ignore</div>':'';
	$delete	= $data['delete']?'<div>delete</div>':'';
	$rel	= json_encode($data['fields']);
?>
<tr class="import_{$data[doc_type]}">
    <td nowrap="nowrap">
<?php /*?>
        <input type="checkbox" name="import[{$id}]" id="ch{$id}" value="{$id}" />
<?php */?>        <label for="ch{$id}">
            {$data[doc_type]}
        </label>
    </td>
    <td nowrap="nowrap">
    {!$document}
    {!$ignore}
    {!$delete}
    </td>
    <td class="name">{$data[name]}</td>
</tr>
<tr class="importData" rel="{$id}">
	<td colspan="2"></td>
    <td></td>
</tr>
<? } ?>
</table>

{!$p}

</form>
<? } ?>


<?
function script_importCommit($val){ ?>
{{script:jq}}
<script src="script/jqImportCommit.js"></script>
<link rel="stylesheet" type="text/css" href="css/jqImportCommit.css">
<? } ?>

<?
//	+function import_rowCommit
function import_rowCommit($val, $data)
{
	setTemplate('');
	$import	= new importBulk();
	$db		= $import->db();
	$ddb	= module('doc');
/*
	if ($val == 'get')
	{
		$data = $db->openID(getValue('id'));
		if ($data) importCommitRowData($ddb, $data);
		return setTemplate('');
	}
*/	
	if ($val == 'set')
	{
		$data = $db->openID(getValue('id'));
		if ($data){
			$d	= getValue('importData');
			dataMerge($d, $data);
			unset($d[$db->key]);
			$db->setValues($db->id(), $d);
		}
		return;
	}

	$data = $db->openID(getValue('id'));
	if (!$data) return;
	$url	= $data['parent_doc_id']?getURL($db->url($data['parent_doc_id'])):'';
?>

<?
$f = $data['fields'] or array();
ksort($f);
showImportArray($f);
?>

<? return; ?>
<table width="100%"><tr>
	<td>
    
<table>
<tr>
    <td>Документ:</td>
    <td><strong>
<? if ($data['doc_id']){ ?>
    {$data[doc_id]}
<? }else{ ?>
	-
<? } ?>
    </strong></td>
</tr>
<tr>
    <td>Родитель:</td>
    <td><strong>
<? if ($url){ ?>
    <a href="{$url}" id="ajax">{$data[parent_doc_id]}</a>
<? }else{ ?>
	-
<? } ?>
    </strong></td>
</tr>
<tr>
    <td>Артикул:</td>
    <td rel="importData[article]"><strong>{$data[article]}</strong></td>
</tr>
<tr>
    <td>Цена:</td>
    <td rel="importData[fields][price]"><strong>{$data[fields][price]}</strong></td>
</tr>
<tr>
    <td>Ед. изм:</td>
    <td><strong>{$data[fields][ed]}</strong></td>
</tr>
<tr>
  <td>Доставка:</td>
  <td><strong>{$data[fields][delivery]}</strong></td>
</tr>
</table>

    </td>
	<td class="importInfo">
<table cellpadding="0" cellspacing="0">
<?
$f = $data['fields'] or array();
ksort($f);
foreach($f as $name=>$val){
	if (is_array($val)) continue;
?>
<tr>
    <td>{$name}: </td>
    <td rel="importData[fields][{$name}]">
    	<strong>{$val}</strong>
    </td>
</tr>
<? } ?>
</table>
    </td>
<? foreach($data['fields'] as $name=>$a){
	if (!is_array($a)) continue;
?>
    <td class="importInfo">
<b>{$name}</b>
<? foreach($a as $name=>$val){ ?>
<div>{$name}: <b>{$val}</b></div>
<? } ?>
    </td>
<? } ?>
</tr>
</table>
<? } ?>

<? function showImportArray($val){
	if (!$val) return;
?>
<ul>
<?	foreach($val as $name => $v){ ?>
<li>{$name}:
<? if (is_array($v)){ ?>
<? showImportArray($v) ?>
<? }else{ ?>
{$v}
<? } ?>
</li>

<? } ?>
</ul>
<? } ?>
