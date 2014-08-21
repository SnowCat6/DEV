<? function layout_admin(&$val, &$data){
?>
{{ajax:template=ajax_layout}}
{{script:jq_ui}}
{{scriptLoad=script/colorpicker-master/jquery.colorpicker.js}}
<link rel="stylesheet" type="text/css" href="script/colorpicker-master/jquery.colorpicker.css">
<style>
.layoutEditor{
	position:fixed;
	top:50px; left:0;
	background:black;
	box-shadow:0 0 10px rgb(0, 0, 0);
	z-index:9999;
}
.layoutEditor *{
	color:white;
}
.layoutEditor input{
	color:black;
}

.layoutEditor .layoutEditorTitle{
	display:block;
	padding:5px 10px;
}
.layoutEditor:hover .layoutEditorTitle{
	display:none;
}
.layoutEditor .layoutEditorHolder{
	display:none;
}
.layoutEditor:hover .layoutEditorHolder{
	display:block;
	min-width:200px;
}
/******************************/
.layoutEditorHolder .layoutRule{
}
.layoutSelected .layoutRule{
	display:none;
}
.layoutEditorHolder .layoutRuleHolder{
	display:none;
}
.layoutEditorHolder .layoutRuleName{
	padding:5px 10px; margin:0;
	font-size:16px;
	font-weight:normal;
}
.layoutEditorHolder .layoutRuleName a{
	text-decoration:none;
}
.layoutEditorHolder .current .layoutRuleHolder{
	display:block;
}
.layoutSelected .layoutCurrent, .layoutCurrent .layoutRuleHolder{
	display:block;
}
.layoutSelected .layoutRuleName{
	background:#666;
}
/******************************/
.layoutEditorHolder .layoutRule2Name{
	padding:5px 10px 5px 20px; margin:0;
	background:#333;
	font-size:12px;
}
.layoutEditorHolder .layoutRule2Name a{
	text-decoration:none;
}
.layoutEditorHolder .layoutRule2Holder{
}
/******************************/
.layoutEditorHolder .layoutEdit{
	padding: 5px 5px 5px 20px;
	border-top:solid 1px #333;
	margin-bottom:5px;
}
.layoutEditorHolder .layoutEdit:first-child{
	border: none;
}
/******************************/
</style>
<script>
//	Правила создания и присвоения CSS стилей
var layoutRules	= {
	//	Перечень названий редактируемых блоков (элементов интерфейса)
	'Страница':	{
		//	Группировка правил по названию внутренних элементов стиля (типы панели редактора)
		'Цвет страницы': {
			//	Тип редактора и участие в в правилах CSS (текст документа)
			'text':			['body'],
			//	Тип редактора и участие в в правилах CSS (фоновые стили)
			'background':	['body']
		},
		'Цвет ссылок': {
			//	Цвет ссылок страницы
			'text': ['a']
		}
	},
	//	Блок мастер-класса
	'Мастер-класс': {
		//	Верхний заголовок в слоте
		'Заголовок':{
			//	Стиль текста заголовка
			'text':			['.slot .bg', '.slot .bg a'],
			//	Фон заголовка
			'background':	['.slot .bg'],
			//	Отступы
			'padding':		['.slot .bg2']
		},
		//	Аннотация мастер-класса, отдельно дя заголовка
		'Заголовок аннотации':{
			//	Стиль текста аннотации
			'text':			['.slot .bg2 h2', '.slot .bg2 h2 a']
		},
		//	Аннотация мастер-класса
		'Аннтотация':{
			//	Стиль текста аннотации
			'text':			['.slot .bg2', '.slot .bg2 a'],
			//	Фон аннотации
			'background':	['.slot .bg2'],
			//	Отступы
			'padding':		['.slot .bg2']
		}
	}
};

var layoutEditors = {
	'background':	layoutBackgroundFn,
	'text':			layoutTextFn
};

$(function()
{
	$(".layoutEditorHolder").html(generateLayoutEditor(layoutRules));
	
	$(".layoutRuleName a").click(function()
	{
		if ($(this).parents(".layoutRule").hasClass("layoutCurrent")){
			$(".layoutEditorHolder").removeClass("layoutSelected");
			$(".layoutEditorHolder .layoutCurrent").removeClass("layoutCurrent");
		}else{
			$(".layoutEditorHolder").addClass("layoutSelected");
			$(".layoutEditorHolder .layoutCurrent").removeClass("layoutCurrent");
			$(this).parents(".layoutRule").addClass("layoutCurrent");
		}
		return false;
	});
	
	$(".layoutEditorColorPicker").uniqueId().colorpicker({
			parts:          'full',
			alpha:          true,
			buttonColorize: true,
        	showNoneButton: true,
			colorFormat : '#HEX',
			select: function(formatted, colorPicker){
				var val = colorPicker.formatted;
				$(this).trigger("change");
			}
    });
	
	for(editorName in layoutEditors){
		var fn = layoutEditors[editorName];
		fn('init');
	}
});
function generateLayoutEditor(rules)
{
	var html = '';
	for(ruleName in rules){
		var rule = rules[ruleName];
		html += '<div class="layoutRule">';
		html += '<h1 class="layoutRuleName"><a href="#">' + ruleName + '</a></h1>';
		html += '<div class="layoutRuleHolder">' + generateLayoutRule(rule) + '</div>';
		html += '</div>';
	}
	return html;
}
function generateLayoutRule(rules)
{
	var html = '';
	for(ruleName in rules){
		var rule = rules[ruleName];
		html += '<div class="layoutRule2">';
		html += '<h2 class="layoutRule2Name">' + ruleName + '</h2>';
		html += '<div class="layoutRule2Holder">' + generateLayoutRuleEdit(rule) + '</div>';
		html += '</div>';
	}
	return html;
}
function generateLayoutRuleEdit(rules)
{
	var html = '';
	for(ruleName in rules){
		var ruleEditor = layoutEditors[ruleName];
		if (ruleEditor == null) continue;
		
		html += '<div class="layoutEdit">';
		html += ruleEditor('html', rules[ruleName]);
		html += '</div>';
	}
	return html;
}
/********************************/
//	BACKGROUND EDITOR
function layoutBackgroundFn(action, rules)
{
	switch(action){
	case 'html': return layoutBackgroundFnHTML(rules);
	case 'init': return layoutBackgroundFnInit(rules);
	}
}
function layoutBackgroundFnHTML(rules)
{
	var rel = JSON.stringify(rules);
	rel = ' rel=\'background:' + rel + '\'';
	
	var html = '';
	html += '<div class="layoutEditorBackground"' + rel + '>';
	html += '<div>Цвет фона: <input type="text" class="input w100 layoutEditorColorPicker" size="8"></div>';
	html += '</div>';
	return html;
}
function layoutBackgroundFnInit(rules)
{
	$(".layoutEditorBackground .input").change(function()
	{
		var rules = $(this).parents(".layoutEditorBackground").attr("rel").split(':', 2);
		rules = $.parseJSON(rules[1]);
		rules = rules.join(',', rules);
		$(rules).css("background-color", $(this).val());
	});
}
/********************************/
//	TEXT EDITOR
function layoutTextFn(action, rules){
	switch(action){
	case 'html': return layoutTextFnHTML(rules);
	case 'init': return layoutTextFnInit(rules);
	}
}
function layoutTextFnHTML(rules)
{
	var rel = JSON.stringify(rules);
	rel = ' rel=\'background:' + rel + '\'';
	
	var html = '';
	html += '<div class="layoutEditorText"' + rel + '>';
	html += '<div>Цвет текста: <input type="text" class="input w100 layoutEditorColorPicker" size="8"></div>';
	html += '</div>';
	return html;
}
function layoutTextFnInit(rules)
{
	$(".layoutEditorText .input").change(function()
	{
		var rules = $(this).parents(".layoutEditorText").attr("rel").split(':', 2);
		rules = $.parseJSON(rules[1]);
		rules = rules.join(',', rules);
		$(rules).css("color", $(this).val());
	});
}
</script>

<div class="layoutEditor">
<div class="layoutEditorTitle">LAYOUT EDITOR</div>
<div class="layoutEditorHolder"></div>
</div>
<? } ?>