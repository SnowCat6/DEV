<?
function holder_tools($val, &$menu)
{
	if (!access('write', "holder:")) return;
	
	$ini	= getIniValue(':');
	if ($ini['designMode'] == 'yes'){
		$menu['Отключить верстку#ajax']	= getURL('admin_holderMode', '');
	}else{
		$menu['Включить верстку#ajax']	= getURL('admin_holderMode', 'mode=yes');
	}
}
//	+function holder_uiMode
function holder_uiMode($val, $data)
{
	if (!access('write', "holder:")) return;
	
	$ini	= getIniValue(':');
	$ini['designMode']	= getValue('mode') == 'yes'?'yes':'';
	setIniValue(':', $ini);
	
	m('page:title', 'Изменение режима разметки');
	if (access('design', 'holder:')){
?>
	Режим редактирования разметки включен
<? }else{ ?>
	Режим редактирования разметки отключен
<? } ?>
<? } ?>