<widget:searchPanel
    category= 'Документы'
    name	= 'Поисковая панель'
    title	= 'Поиск товаров и документов по запросу'
    cap		= 'searchPanel'
    exec	= "widget:searchPanel=[@data.selector];options:[data.options];options.result:[data.result]"
>
<cfg:data.result	    			name = 'Результат поиска' default = 'search' />
<cfg:data.selector	    			name = 'Выбор документов' default = 'type:article,product' />
<cfg:data.options.names		name = 'Названия характеристик' />
<cfg:data.options.groups		name = 'Названия групп свойств' default = 'productSearch' />
<cfg:data.options.hasChoose	name = 'Показывать панель выбора' type="checkbox" checked= '1' />

<? function widget_searchPanel($id, $search){ ?>

<?
global $_CONFIG;
$result								= $search['options']['result'];
$_CONFIG['searchResult'][$result]	= module("doc:searchPanel", $search);
?>

<? } ?>
</widget:searchPanel>


<widget:searchPanel2
    category= 'Документы'
    name	= 'Поисковая панель2'
    title	= 'Поиск товаров и документов по запросу'
    cap		= 'searchPanel'
    exec	= "widget:searchPanel2=[@data.selector];options:[data.options];options.result:[data.result]"
>
<cfg:data.result	    	name = 'Результат поиска' default = 'search' />
<cfg:data.selector	    	name = 'Выбор документов' default = 'type:article,product' />
<cfg:data.options.names		name = 'Названия характеристик' />
<cfg:data.options.groups	name = 'Названия групп свойств' default = 'productSearch' />
<cfg:data.options.hasChoose	name = 'Показывать панель выбора' type="checkbox" checked= '1' />

<?
//	+function widget_searchPanel2
function widget_searchPanel2($id, $search){ ?>

<?
global $_CONFIG;
$result								= $search['options']['result'];
$_CONFIG['searchResult'][$result]	= module("doc:searchPanel:default2", $search);
?>

<? } ?>
</widget:searchPanel2>