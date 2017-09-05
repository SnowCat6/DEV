<? function doc_property_uploadFull($data)
{
	m('script:jq_ui');
	m('script:fileUpload');
	
	$folders	= array(
		'gallery:upload:Title'		=> 'Обложка документа',
		'gallery:uploadFull:Image'	=> 'Фотогаллерея',
		'gallery:uploadFull:Gallery'=> 'Изображения в документе',
		'gallery:uploadFull:File'	=> 'Файлы документа'
	);
	$ev	= array(
		'data'		=> $data,
		'folders'	=> &$folders
	);
	event('document.property_uploadFull', $ev);
?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css" />
<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
    <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
<? foreach($folders as $module=>$name){ ?>
        <li class="ui-corner-top"><a href="#{$module}">{$name}</a></li>
<? } ?>
    </ul>

<? foreach($folders as $module=>$name){ ?>
    <div id="{$module}" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
    <? module($module, $data) ?>
    </div>
<? } ?>

</div>

{{script:adminTabs}}
<? return '2-Изображения и файлы'; } ?>