<widget:read
    category= 'Информация'
    name	= 'Редактируемая зона'
    desc	= 'Текстовой блок для размещения HTML с визуальным редактором'
    cap		= "read"
    exec	= 'read:[id]'
    delete	= 'read_delete:[id]'
>
</widget:read>

<widget:table
    category= 'Информация'
    name	= 'Таблица'
    desc	= 'Табличные данные'
    cap		= "read"
    exec	= 'table:[id]=fx:[data.fx]'
    delete	= 'read_delete:[id]'
>
<cfg:data.fx name="FX" />
</widget:table>
