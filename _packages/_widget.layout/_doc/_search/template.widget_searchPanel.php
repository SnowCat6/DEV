<widget:searchPanel
    category= 'Документы.Поиск'
    name	= 'Поисковая панель'
    title	= 'Поиск товаров и документов по запросу'
    cap		= 'searchPanel'
    exec	= "doc:searchPanel:[data.template]=[@data.selector];options:[data.options]"
>
<cfg:data.template			name = 'Вид панели' type="select" select= 'default,default2' default='default' />
<cfg:data.options.result	name = 'Результат поиска' default = 'search' />
<cfg:data.selector	    	name = 'Выбор документов' default = 'type:article,product' />
<cfg:data.options.names		name = 'Названия характеристик' />
<cfg:data.options.groups	name = 'Названия групп свойств' default = 'productSearch' />
<cfg:data.options.hasChoose	name = 'Показывать панель выбора' type="checkbox" checked= '1' />

</widget:searchPanel>
