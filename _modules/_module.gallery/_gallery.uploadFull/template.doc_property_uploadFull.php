<? function doc_property_uploadFull($data){ module('script:jq_ui');	?>
<div id="fileTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#uploadImage">Изображения</a></li>
    <li class="ui-corner-top"><a href="#uploadGallery">Фотогаллерея</a></li>
    <li class="ui-corner-top"><a href="#uploadFile">Файлы</a></li>
</ul>

<div id="uploadImage" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<? module('gallery:uploadFull:Image', $data) ?></div>
<div id="uploadGallery" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<? module('gallery:uploadFull:Gallery', $data) ?></div>
<div id="uploadFile" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<? module('gallery:uploadFull:File', $data) ?></div>
</div>

<script>
$(function() {
	$("#fileTabs").tabs();
});
</script>

<? return '20-Файлы'; } ?>