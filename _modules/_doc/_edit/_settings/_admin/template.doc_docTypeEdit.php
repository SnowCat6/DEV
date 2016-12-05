<?
//	+function doc_doctypeEdit
function doc_doctypeEdit($val)
{
	if (!hasAccessRole('developer')) return;
	
	m('ajax:template', 'ajax_edit');
	$type	= getValue('type');

	if ($type)
	{	
		list($typeName, $templateName)	= explode(':', $type);
		$data	= docConfig::getTemplate($type);
	}else{
		$typeType		= trim(getValue('typeType'), ':');
		$typeTemplate	= trim(getValue('typeTemplate'), ':');
		if ($typeType) $type = "$typeType:$typeTemplate";

		$data		= docConfig::getTemplate($type);
		if (!$data) $data = array();
		else $type = '';
	}

	$d = getValue('docConfig');
	if ($d && $type)
	{
		$d['id']	= $type;
		dataMerge($d, $data);

	    moduleEx('admin:tabUpdate:docConfig_', $d);
		if (!$d) return;

		docConfig::setTemplate($type, $d);
		$data	= docConfig::getTemplate($type);
		messageBox('Данные сохранены');
	}
	$name	= docType($type);
	$data['id']	= $type;
?>
<module:script:ajaxForm />
<module:page:title @="Настройки типа документа $name - $type" />

<form action="{{url:#}}" method="post" class="ajaxForm ajaxReload">
    <module:admin:tab:docConfig_ @="$data" />
</form>

<? } ?>
