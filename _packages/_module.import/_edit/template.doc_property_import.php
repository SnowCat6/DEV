<? function doc_property_import(&$data){
	$db		= module('doc', $data);
	$id		= $db->id();
	
	$fields	= $data['fields'];
	$any	= $fields['any'];
	$import	= $any['import'];
	if (!$import) $import = array();
?>
<style>
.importData td, .importData th{
	vertical-align:top;
}
.importData div div{
	padding-left:40px;
}
.importData b{
	color:green;
	margin-right:10px;
}
</style>
<table border="0" cellspacing="0" cellpadding="0" class="table table2 importData">
<? foreach($import as $name=>&$val){
	$val	= makeImportVal($val);
	if (is_array($val)){
		$v = $val;
		foreach($v as $n => $v2){
			$n	= htmlspecialchars($n);
			$v2	= htmlspecialchars($v2);
			$val= "<div>$n: $v2</div>";
		}
	}else{
		$v	= htmlspecialchars($v);
	}
?>
<tr>
    <th>{$name}</th>
    <td>{!$val}</td>
</tr>
<? } ?>
</table>
<? return '8-Импорт'; }?>
<? function makeImportVal($val){
	if (!is_array($val))
		return htmlspecialchars($val);

	$r	= '';
	foreach($val as $n=>$v){
		$r .= "<div><b>$n</b>" . makeImportVal($v) . '</div>';
	}
	return $r;
}?>