<? function doc_property_uploadFull($data)
{
	m('script:jq_ui');
	m('script:fileUpload');
?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css" />
<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#uploadTitle">Обложка документа</a></li>
    <li class="ui-corner-top"><a href="#uploadGallery">Фотогаллерея</a></li>
    <li class="ui-corner-top"><a href="#uploadImage">Изображения в документе</a></li>
    <li class="ui-corner-top"><a href="#uploadFile">Файлы документа</a></li>
</ul>

<div id="uploadTitle" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<? module("gallery:upload:Title", $data) ?>
</div>

<div id="uploadImage" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<? module('gallery:uploadFull:Image', $data) ?>
</div>

<div id="uploadGallery" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<? module('gallery:uploadFull:Gallery', $data) ?>
</div>

<div id="uploadFile" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<? module('gallery:uploadFull:File', $data) ?>
</div>
</div>

{{script:adminTabs}}
<? return '2-Изображения и файлы'; } ?>