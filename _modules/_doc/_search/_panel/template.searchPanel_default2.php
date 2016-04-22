<?
function searchPanel_default2($data, $props)
{
	
	$options	= $data['options'];
	$qs			= $options['qs'];
	$search		= $options['search'];
	$searchName	= $options['searchName'];
	$baseURL	= $options['url']?$options['url']:'#';
?>
<link rel="stylesheet" type="text/css" href="css/searchPanel.css">

<div class="searchPanel searchPanel2">
<? ob_start() ?>


<? if ($options['choose']){ ?>
<div class="searchChoose">
	<div class="title">
        <big>Ваш выбор:</big>
        <a href="{{getURL:$baseURL}}" class="clear">очистить</a>
    </div>
    <div class="searchProperty">
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
    </div>
</div>
<? } ?>

<? foreach($props as $propertyName => $values)
{
	$data['options']['propertyName']= $propertyName;
	$data['options']['values']		= $values;
	$values	= mEx("prop:selector:$propertyName", $data);
?>
<div class="searchProperty">

<h3>{$data[options][propertyName]}</h3>
<div>{!$values}</div>
</div>
<? } ?>

<? $p = ob_get_clean(); ?>

<? if (is_array($data['options']['buttons'])){ ?>
<form action="{{url:$baseURL}}" method="post">
<?= makeFormInput($options['hidden'], $searchName)?>
<?= $p ?>

<div class="panelButtons">
<? foreach($data['options']['buttons'] as $type => $name){ ?>
    <input type="{$type}" class="{button_$type}" value="{$name}" />
<? } ?>
</div>

</form>
<? }else{
	echo $p;
}?>

</div>
<? } ?>