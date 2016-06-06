<?
//	+function docConfig_property_update
function docConfig_property_update(&$data)
{
	if (!hasAccessRole('developer')) return;
	
	$prop	= array();
	
	$p		= getValue('docProperty');
	$pName	= $p['name'];
	if (!is_array($pName)) $pName = array();
	foreach($pName as $ix => $name)
	{
		$name	= trim($name);
		if (!$name) continue;
		$prop[$name] 	= trim($p['value'][$ix]);
	}
	$data['property']	= $prop;
}
?>
<?
//	+function docConfig_property
function docConfig_property($data)
{
	$prop	= $data['property'];
	if (!is_array($prop)) $prop = array();
?>
<table class="table" width="100%" cellpadding="0" cellspacing="0">
<tr>
	<th>Название</th>
    <th width="100%">Значение</th>
</tr>
<? foreach($prop as $name => $default){ ?>
<tr>
  <td><input type="text" class="input" name="docProperty[name][]" value="{$name}" /></td>
  <td><input type="text" class="input w100" name="docProperty[value][]" value="{$default}" /></td>
</tr>
<? } ?>
<? for($i=0; $i<4; ++$i){ ?>
<tr>
  <td><input type="text" class="input" name="docProperty[name][]" /></td>
  <td><input type="text" class="input w100" name="docProperty[value][]" /></td>
</tr>
<? } ?>
</table>

<? return 'Характеристики по умолчанию'; } ?>