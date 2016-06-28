<?
//	+function _holderInput_doc_filter_update
function _holderInput_doc_filter_update($holder, $name, $val)
{
	$value	= array();
	foreach($val as $n => $v){
		$value[]	= "$n:$v";
	}
	return implode(';', $value);
}
//	+function _holderInput_doc_filter
function _holderInput_doc_filter($holder, $name, $cfg)
{
	$val	= array();
	setDataValues($val,	$cfg['value']);
	$def	= array();
	setDataValues($def,	$cfg['default']);
	
	$input				= array();
	$input['Родитель']	= 'parent';
	$input['Тип']		= 'type';
	$input['Шаблон']	= 'template';
//	$input['Свойства']	= 'property';
	
	foreach($def as $n => $v){
		if (array_search($n, $input)) continue;
		$input[$n]	= $n;
	}
?>
<table>
<? foreach($input as $n => $v)
{
	$value	= $val[$v];
	$default= $def[$v];
?>
<tr>
   	<td>{$n}: </td>
    <td><input type="text" class="input w100" name="widgetConfig[{$name}][{$v}]" value="{$value}" placeholder="{$default}" /></td>
</tr>
<? } ?>
</table>

<? } ?>
