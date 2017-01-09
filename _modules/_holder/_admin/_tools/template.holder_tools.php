<?
function holder_tools($val, &$menu)
{
	if (!access('write', "holder:")) return;
	
	if (access('design', 'holder:')){
		$menu['Отключить верстку#ajax']	= getURL('admin_holderMode', '');
	}else{
		$menu['Включить верстку#ajax']	= getURL('admin_holderMode', 'mode=yes');
	}
}
//	+function holder_uiMode
function holder_uiMode($val, $data)
{
	if (!access('write', "holder:")) return;
	
	$mode	= getValue('mode') == 'yes'?'yes':'';
	$id		= userID();
	setStorage('designMode', $mode, "user$id");
	
	m('page:title', 'Изменение режима разметки');
	if (access('design', 'holder:')){
?>
	Режим редактирования разметки включен
<? }else{ ?>
	Режим редактирования разметки отключен
<? } ?>

<module:script:reload />

<? } ?>

<?
//	+function holder_editTools
function holder_editTools($val, &$menu)
{
	$holders	= config::get(":adminHolders", array());
	foreach($holders as $holder)
	{
		$menu["holder: $holder#ajax"]	= getURL("admin_holderEdit", "holderName=$holder");
	}
}
?>
