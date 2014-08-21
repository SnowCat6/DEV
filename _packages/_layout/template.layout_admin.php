<? function layout_admin(&$val, &$data)
{
	$rules	= getLayoutStyles();
	
	$ini	= getCacheValue('ini');
	$styles	= unserialize($ini[':layoutStyle']['rules']);
	if (!is_array($styles)) $styles = array();
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
.layoutSave{
	display:none;
}
.layoutEditor:hover .layoutSave{
	display:block;
}
.layoutSave input{
	padding:2px 5px;
	border:none;
}
</style>
<script>
/*
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
*/
var layoutRules	= <?= json_encode($rules)?>;
var layoutStyles = new Array();

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
	
	for(editorName in layoutEditors)
	{
		var fn = layoutEditors[editorName];
		fn('init', <?= json_encode($styles)?>);
	}
	$(".layoutButton").click(function()
	{
		var data = '';
		for(ruleName in layoutStyles)
		{
			var styles = layoutStyles[ruleName];
			for(styleName in styles){
				if (data) data += '&';
				data += 'rules[' + escape(ruleName) + '][' +  escape(styleName) + ']=' + escape(styles[styleName]);
			}
		}
		$.get("{{url:layout_update}}", data);
	});
});

function updateLayoutRule()
{
	var ruleCSS = '';
	for(rulesName in layoutStyles)
	{
		ruleCSS += rulesName + '{ ';
		var styles = layoutStyles[rulesName];
		for(styleName in styles){
			var value = styles[styleName];
			if (value){
				ruleCSS += styleName + ': ' + styles[styleName] + ' !important; ';
			}
		}
		ruleCSS += " }\r\n";
	}
	$("#layoutEditorCSS").remove();
	$('<style type="text/css" id="layoutEditorCSS">').html(ruleCSS)
    .appendTo("head");
}

function addLayoutRule(ruleNames, ruleValues)
{
	ruleNames = $.parseJSON(ruleNames);
	ruleNames = ruleNames.join(', ');
	
	if (layoutStyles[ruleNames] == null){
		layoutStyles[ruleNames] = new Array();
	}
	for(styleName in ruleValues){
		layoutStyles[ruleNames][styleName] = ruleValues[styleName];
	}
}

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

		rel = ' rel=\'' + JSON.stringify(rules[ruleName]) + '\'';
		
		html += '<div class="layoutEdit ' + ruleName +'"' + rel + '>';
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
	var html = '';
	html += '<div>Цвет фона: <input type="text" class="input w100 layoutEditorColorPicker" size="8"></div>';
	return html;
}
function layoutBackgroundFnInit(rules)
{
	$(".layoutEdit.background .input").change(function()
	{
		var rules = $(this).parents(".layoutEdit").attr("rel");
		addLayoutRule(rules, {
			"background-color": $(this).val()
		});
		updateLayoutRule();
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
	var html = '';
	html += '<div>Цвет текста: <input type="text" class="input w100 layoutEditorColorPicker" size="8"></div>';
	return html;
}
function layoutTextFnInit(rules)
{
	$(".layoutEdit.text .input").change(function()
	{
		var rules = $(this).parents(".layoutEdit").attr("rel");
		addLayoutRule(rules, {
			"color": $(this).val()
		});
		updateLayoutRule();
	});
}
</script>

<div class="layoutEditor">
<div class="layoutEditorTitle">LAYOUT EDITOR</div>
<div class="layoutEditorHolder"></div>
<div class="layoutSave"><input type="button" class="layoutButton w100" value="Сохранить" /></div>
</div>
<? } ?>
<? function getLayoutStyles()
{
	$rules	= array();
	$styles	= getSiteFiles('', '\.css$');
	foreach($styles as $path)
	{
		$style	= file_get_contents($path);
		if (!preg_match_all("#/\*(.*?\*/)#s", $style, $val)) continue;
		foreach($val[1] as $v)
		{
			if (!preg_match('# (.+):#', $v, $v2)) continue;
			if (!list($styleName, $val) = explode(':', $v, 2)) continue;
			
			$val	= explode("\r\n", $val);
			foreach($val as $row)
			{
				if (!list($ruleName, $ruleValue) = explode(':', $row, 2)) continue;
				$ruleName = trim($ruleName);
				if (!$ruleName || !$ruleValue) continue;
				
				$ruleValue	= explode(';', $ruleValue);
				foreach($ruleValue as $row){
					if (!preg_match('#(.+)\((.*)\)#', $row, $row)) continue;
					$v	= explode(',', $row[2]);
					foreach($v as $v2){
						$v2	= trim($v2);
						$v3	= array();
						foreach(explode(',', $row[1]) as $v){
							$v = trim($v);
							if ($v) $v3[] = $v;
						}
						if ($v2 && $v3) $rules[$styleName][$ruleName][$v2] = $v3;
					}
				}
			}
		}
	}
	return $rules;
}
?>