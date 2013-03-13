<?
function module_script_ajax($val, &$config)
{
	if (testValue('ajax')){
		$ajaxTemplate = getValue('ajax');
		$config['page']['template'] = $ajaxTemplate?"page.$ajaxTemplate":'page.ajax';
	}
}?>
