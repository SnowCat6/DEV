<?
function prop_selector_default($propertyName, $data)
{
	$options	= $data['options'];
	$qs			= $options['qs'];
	$searchName	= $options['searchName'];
	
	foreach($options['values'] as $valueName => $count)
	{
		$s1	= $qs;
		if ($s1[$searchName]['prop'][$propertyName] == $valueName){
			$s1[$searchName]['prop'][$propertyName]	= '';
			$class	= ' class="current"';
		}else{
			$s1[$searchName]['prop'][$propertyName]	= $valueName;
			$class	= '';
		}
		removeEmpty($s1);
		$s1	= makeQueryString($s1);
		$val= propFormat($valueName, $propertyName);
	?>
	<a href="{{url:#=$s1}}"{!$class}><span>{!$val}</span> <sup>{$count}</sup></a>
	<? } ?>
<? } ?>