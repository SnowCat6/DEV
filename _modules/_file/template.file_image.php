<?
//	Вывод и манипуляция изображениями на сайте
//	{{file:imge:doc$id=mask:design/mask.png;hasAdmin=true;attribute.style.class:color;adminMenu:$menu}}
function file_image(&$storeID, &$data)
{
	$storage	= array();
	$ev			= array(
		'id'	=> $storeID,
		'name'	=> 'fileImage',
		'content'	=> &$storage);
	//	ПОлучить локальное хранилище для манипуляций изображением и настройки
	event('storage.get', $ev);

	//	Сохранить настройки в хранилище
	event('storage.set', $ev);
}?>