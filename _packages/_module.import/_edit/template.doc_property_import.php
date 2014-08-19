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
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <th nowrap="nowrap">Артикул</th>
        <td width="100%">
        <input type="text" name="doc[+fields][any][import][:importArticle]" value="{$import[:importArticle]}" class="input w100">
        </td>
      </tr>
      <tr>
        <th nowrap="nowrap"><label for="importDelivery">Под заказ</label></th>
        <td>
<input type="hidden" name="doc[+fields][any][import][:raw][delivery]" value="">
<input type="checkbox" name="doc[+fields][any][import][:raw][delivery]" id="importDelivery" 
	value="под заказ" {checked:$import[:raw][delivery]=="под заказ"}>
        </td>
      </tr>
    </table></td>
    <td width="50%" valign="top">
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

    </td>
  </tr>
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