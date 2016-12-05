<?
//	+function doc_docConfig
function doc_docConfig($ini)
{
	if (!hasAccessRole('developer')) return;
//	if (getValue('docRules')) doc_docConfig_update();
	$delete	= getValue('typeDelete');
	if ($delete){
		docConfig::deleteTemplate($delete);
	}
	$types	=docConfig::getTypeFilter();
	m("ajax:template", "ajax_edit");
?>
<module:script:ajaxForm />
<module:page:title @="Настройки отображения видов страниц" />
<link rel="stylesheet" type="text/css" href="css/docType.css">

<module:script:adminTabs />
<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
<? foreach($types as $name=>$filter){
	$id	= md5($name);
?>
    <li class="ui-corner-top"><a href="#{$id}">{$name}</a></li>
<? } ?>
	<li class="ui-corner-top">
    	<a href="{{url:admin_doctype}}" id="ajax">Новый тип документа</a>
	</li>
</ul>

<? foreach($types as $name=>$filter){
	$id	= md5($name);
?>
<div id="{$id}">
    <table class="table adminDocType" width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <th nowrap>Тип</th>
      <th>Название типа документа</th>
      <th>Краткое описание</th>
      <th nowrap align="right">&nbsp;</th>
    </tr>
    <?
    $rules	= docConfig::getTemplates($filter);
    foreach($rules as $type => $data){ ?>
    <tr>
        <td nowrap width="5%"><a id="ajax" href="{{url:admin_doctype=type:$type}}">{$type}</a></td>
        <td>{$data[NameOne]}</td>
        <td>{$data[note]}</td>
        <td nowrap align="right">
    <? if ($data['type'] == 'internal'){ ?>
            <a href="{{url:admin_docconfig=typeDelete:$type}}" id="ajax">сбросить настройки</a>
    <? }else{ ?>
            <a href="{{url:admin_docconfig=typeDelete:$type}}" id="ajax">удалить</a>
    <? } ?>
        </td>
    </tr>
    <? } ?>
    </table>
</div>
<? } ?>
</div>
<? } ?>


<?
//	+function doc_toolsConfig
function doc_toolsConfig($db, $val, &$menu)
{
	if (!access('write', 'doc:')) return;
	if (!hasAccessRole('developer')) return;
	
	$menu["Типы документов#ajax"]	= getURL('admin_docConfig');
}
?>