<widget:documentPage
	category= "Документы"
	name	= "Страница документа"
    note	= "Вывод текста страницы"
	exec	= "doc:page=[@data.selector]"
    cap		= "document"
>
<cfg:data.selector	name = 'Фильтр документов' type = 'doc_filter' default = 'id:this' />

</widget:documentPage>
