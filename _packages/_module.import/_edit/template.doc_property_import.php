<? function doc_property_import(&$data){
	$db		= module('doc', $data);
	$id		= $db->id();
	
	$fields	= $data['fields'];
	$any	= $fields['any'];
	$import	= $any['import'];
	if (!$import) $import = array();
?>
<table border="0" cellspacing="0" cellpadding="0" class="table table2">
<? foreach($import as $name=>&$val){ ?>
<tr>
    <th>{$name}</th>
    <td>{$val}</td>
</tr>
<? } ?>
</table>
<? return '8-Импорт'; }?>