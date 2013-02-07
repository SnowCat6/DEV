<? function doc_property_publish_update($data)
{
	$thisParent	= array();
	$parentToAdd= getValue('parentToAdd');
	if (is_array($parentToAdd))
	{
		foreach($parentToAdd as $parentID){
			if (!$parentID) continue;
			$thisParent[] = $parentID;
		}
	}

	$data[':property'][':parent'] = implode(', ', $thisParent);
}
?>
<? function doc_property_publish($data){?>
<?
	$db		= module('doc', $data);
	$id		= $db->id();
	$type	= $data['doc_type'];
	module('script:calendar');
	
	if (!$id){
		if ($type == 'article' || $type == 'product')
			$data['datePublish'] = date('d.m.Y');
	}else{
		$date = makeDate($data['datePublish']);
		if ($date) $data['datePublish'] = date('d.m.Y H:i', $date);
	}
	$folder	= $db->folder();
	$folder	= "$folder/Gallery";
?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<tr>
    <td width="33%" valign="top">
Дата публикации
<div><input name="doc[datePublish]" type="text" value="{$data[datePublish]}" class="input w100" id="calendarPublish" /></div>
    </td>
    <td width="33%" valign="top">
<div><? module("gallery:upload:documentTitle", $data) ?></div>
    </td>
    <td width="33%" valign="top">
Добавить к документам:
<table width="100%" cellpadding="0" cellspacing="0">
<?
$parentToAdd	= array();
$parentTypes	= getCacheValue('docTypes');
foreach($parentTypes as $parentType=>$val){
	if (access('add', "doc:$parentType:$type"))
		$parentToAdd[] = $parentType;
};
$parentToAdd = implode(', ', $parentToAdd);

$prop	= module("prop:get:$id");
@$prop	= explode(', ', $prop[':parent']['property']);

$s			= array();
$sql		= array();
$s['type'] 	= $parentToAdd;
doc_sql($sql, $s);

$ddb	= module('doc', $data);
$ddb->open($sql);
while($d = $ddb->next()){
	$iid = $ddb->id();
	if ($iid == $id) coninue;
	@$class	= is_int(array_search($iid, $prop))?' checked="checked"':'';
?>
<tr>
	<th><input name="parentToAdd[]" id="parent{$iid}" type="checkbox" value="{$iid}"{!$class} /></th>
	<td width="100%"><label for="parent{$iid}">{$d[title]}</label></td>
</tr>
<? } ?>
</table>
    </td>
</tr>
</table>

<? return '10-Публикация'; } ?>