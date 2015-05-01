<?
function searchPanel_default($data, $props)
{
	$options	= $data['options'];
	$qs			= $options['qs'];
	$search		= $options['search'];
	$searchName	= $options['searchName'];
	$baseURL	= $options['url']?$options['url']:'#';
?>
<link rel="stylesheet" type="text/css" href="css/searchPanel.css">
<div class="searchPanel">

<table class="property" width="100%" cellpadding="0" cellspacing="0">

<tr>
<td colspan="2" class="searchChoose">
	<span class="title">
        <big>Ваш выбор:</big>
    </span>
    <span class="searchProperty">
<?
//	Выведем уже имеющиеся в поиске варианты
foreach($options['choose'] as $name => $val)
{
	//	Сделаем ссылку поиска но без текущего элемента
	$s1								= $qs;
	$s1[$searchName]['prop'][$name]	= '';
	removeEmpty($s1);
	$url	= getURL($baseURL, makeQueryString($s1));
	$val	= propFormat($val, $name);
	//	Покажем значение
?>
    <a href="{$url}" title="{$name}">{!$val}</a>
<? } ?>
	</span>

<? if ($options['choose']){ ?>
	<a href="{{getURL:$baseURL}}" class="clear">очистить</a>
<? } ?>
</td>
</tr>


<? foreach($props as $propertyName => $values){?>
<tr>
	<th title="{$note}">{$propertyName}</th>
    <td width="100%" class="searchProperty">
<?
$data['options']['values']	= $values;
moduleEx("prop:selector:$propertyName", $data);
?>
    </td>
</tr>
<? } ?>
</table>

</div>
<? } ?>