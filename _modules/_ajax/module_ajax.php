<?
function module_ajax($val, &$data)
{
	$fn = getFn("ajax_$val");
	return $fn?$fn($data):NULL;
}
function ajax_read($data){
	@$template = $data[1];
	module("doc:read:$template", getValue('search'));
}
function ajax_template(&$data)
{
	if (!testValue('ajax')) return;
	if (is_array($data)) $data = implode('', $data);
	if ($data) setTemplate($data);
	return true;
}

//	Обработчик страницы, если передано значение ajax, то меняет стандартный шаблон выводимого документа на AJAX шаблон
function module_script_ajax($val, &$config)
{
	if (!testValue('ajax')) return;

	$ajaxTemplate = getValue('ajax');
	setTemplate($ajaxTemplate?$ajaxTemplate:'ajax');
}?>
