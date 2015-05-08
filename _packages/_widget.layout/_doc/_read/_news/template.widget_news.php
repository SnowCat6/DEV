<widget:news1
	category= "Документы"
	name	= "Новости с картинкой"
    note	= "Список документов с картинкой и текстом"
	exec	= "doc:read:news3=[@data.selector];max:[data.max]"
    cap		= "documents"
>
<cfg:data.selector	name = 'Фильтр документов' type = 'doc_filter' default = 'id:this' />
<cfg:data.max		name = 'Мксимальное количество' default = '3' />

</widget:news1>