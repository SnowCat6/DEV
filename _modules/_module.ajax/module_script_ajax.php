<?
//	Обработчик страницы, если передано значение ajax, то меняет стандартный шаблон выводимого документа на AJAX шаблон
function module_script_ajax($val, &$config)
{
	if (!testValue('ajax')) return;

	$ajaxTemplate = getValue('ajax');
	setTemplate($ajaxTemplate?$ajaxTemplate:'ajax');
}?>
