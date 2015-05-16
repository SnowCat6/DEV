<widget:searchPanel
    category= 'Документы.Поиск'
    name	= 'Поисковая панель'
    title	= 'Поиск товаров и документов по запросу'
    cap		= 'searchPanel'
    exec	= "doc:searchPanel:[data.template]=[@data.selector];options:[data.options]"
    cfg		= "widget:searchPanel_updateConfig:$id"
>
<cfg:data.template			name = 'Вид панели' type="select" select= '' default='default' />
<cfg:data.options.result	name = 'Результат поиска' default = 'search' />
<cfg:data.selector	    	name = 'Выбор документов' default = 'type:article,product' />
<cfg:data.options.names		name = 'Названия характеристик' />
<cfg:data.options.groups	name = 'Названия групп свойств' default = 'productSearch' />
<cfg:data.options.hasChoose	name = 'Показывать выбранное' type="checkbox" checked= '1' default="0" />

<?
//	+function widget_searchPanel_updateConfig
function widget_searchPanel_updateConfig($id, &$widget)
{
	$n	= array();
	$res=	module("findTemplates:^searchPanel_", '(_update|_config)$');
	foreach($res as $name => $path){
		$name	= substr($name, strlen('searchPanel_'));
		$n[]	= $name;
	}
	$widget['config']['data.template']['select']	= implode(',', $n);
}?>

</widget:searchPanel>
