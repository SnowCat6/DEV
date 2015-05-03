<? function holder_uiEdit($val, $data)
{
	$holderName	= getValue('holderName');
	if (!$holderName) return;
	
	if (!access('write', "holder:$holderName")) return;

	beginUndo();
	$holderSort	= getValue('holderSort');
	if (is_array($holderSort))
	{
		$newWidgets	= array();
		$widgets	= module("holderAdmin:getHolderWidgets:$holderName");
		foreach($holderSort as $oldIndex)
		{
			$widget	= $widgets[$oldIndex];
			if ($widget) $newWidgets[]	= $widget;
		}
		$widgets	= $newWidgets;
		module("holderAdmin:setHolderWidgets:$holderName", $widgets);
	}
	
	$holderDelete	= getValue('holderDelete');
	if (is_array($holderDelete))
	{
		$widgets	= module("holderAdmin:getHolderWidgets:$holderName");
		foreach($holderDelete as $ix){
			$widgets[$ix] = '';
			unset($widgets[$ix]);
		}
		module("holderAdmin:setHolderWidgets:$holderName", $widgets);
		echo 111;
	}

	$widgetDelete	= getValue('widgetDelete');
	if (is_array($widgetDelete))
	{
		$widgets	= module("holderAdmin:getWidgets");
		foreach($widgetDelete as $widgetID){
			$widgets[$widgetID]	= '';
			unset($widgets[$widgetID]);
		}
		module("holderAdmin:setWidgets", $widgets);
	}

	$widgetAdd	= getValue('widgetAdd');
	if ($widgetAdd){
		$widget	= module("holderAdmin:getWidget:$widgetAdd");
		m("holderAdmin:addWidget:$holderName", $widget);
	}
	
	$widgetData	= getValue('widgetData');
	if (is_array($widgetData)){
		m("holderAdmin:addWidget:$holderName", $widgetData);
	}
	endUndo();
	
	$preview= array('preview_prefix' => 'widget_preview_');
	$json	=json_encode($preview);
	
	$holders= getStorage('holder/holders', 'ini');
	$data	= getValue("holder");
	if (is_array($data))
	{
		foreach($data as $name => $value){
			$holders[$holderName][$name]	= $value;
		}
		setStorage('holder/holders', $holders, 'ini');
	}
//////////////////////////////////////////////////	
?>
{{page:title=Выберите виджет для добавления к $holderName}}

{{script:ajaxLink}}
{{script:preview}}
{{script:jq_ui}}

<script>
$(function(){
	$(".holderAdminSort").sortable({
		axis: 'y'
	});
});
</script>

<form action="{{url:#=holderName:$holderName}}" method="post" class="ajaxForm ajaxReload">

<table class="table" width="100%">
  <tr class="noBorder">
    
  <td width="300" valign="top" nowrap="nowrap" class="holderAdminSort">
  <?
$widgets	= module("holderAdmin:getHolderWidgets:$holderName");
foreach($widgets as $ix => $widget){
	$widget	= module("holderAdmin:widgetPrepare", $widget);
?>
  <div>
    <span class="ui-icon ui-icon-arrowthick-2-n-s admin_sort_handle" style="float:left"></span>
    <a href="{{url:admin_holderWidgetEdit=holderName:$holderName;widgetID:$widget[id]}}" id="ajax">cfg</a>
    <label title="{$widget[desc]}"><input type="checkbox" name="holderDelete[]" value="{$ix}" />
      {$widget[name]}
      </label>
    <input type="hidden" name="holderSort[]" value="{$ix}" />
  <? if ($widget['note']){ ?>
    <blockquote>{$widget[note]}</blockquote>
  <? } ?>
  </div>
  <? } ?>
  </td>
    
  <td valign="top" class="adminWidgetTabs">
  <? module('admin:tab:holder_tab', $holderName) ?>
  </td>
</tr>
</table>
</form>

{{script:jq}}
<script>
$(function(){
	$('.holderTrigger > h3').click(function(){
		$(this).parent().toggleClass('selected');
	});
});
</script>
<style>
.adminWidgetTabs{
}
.adminWidgetTabs a{
	color:white;
	margin-right: 20px;
	line-height:150%;
}
.holderTrigger h3{
	margin-top:0;
	margin-bottom:10px;
	font-weight:normal;
	cursor:pointer;
}
.holderTrigger.selected h3{
	border-bottom:solid 1px #888;
}
.holderTrigger .content{
	display:none;
	margin:0; padding:0;
	margin-bottom:20px;
}
.holderTrigger.selected .content{
	display:block;
}
</style>
<? } ?>

<?
//	+function holder_tabLib
function holder_tabLib($holderName)
{
$preview= array('preview_prefix' => 'widget_preview_');
$json	=json_encode($preview);

$rawWidgets	= array();
event('holder.widgets', $rawWidgets);
$count	= count($rawWidgets);

$wMenu	= array();
foreach($rawWidgets as $w)
	$wMenu[$w['category']][]	= $w;

foreach($wMenu as $wCategory => $widgets){ ?>
<div class="menu inline holderTrigger seekLink">
    <h3>{$wCategory} <sup><?= count($widgets)?></sup></h3>
    <div class="content">
  <? foreach($widgets as $widget){?>
        <a href="{{url:#=holderName:$holderName;widgetData:$widget}}" title="{$widget[desc]}" class="preview" rel="{$json}">
        	{$widget[name]}
        </a>
  <? } ?>
    </div>	
</div>
<? } ?>

<? return "Библиотека ($count)"; } ?>

<?
//	+function holder_tabExists
function holder_tabExists($holderName)
{
$holders	= getStorage('holder/holders', 'ini') or array();
$counters	= array();
foreach($holders as $holder)
{
	$widgetsID	= $holder['widgets'] or array();
	foreach($widgetsID as $widgetID)
		$counters[$widgetID]++;
}

$preview= array('preview_prefix' => 'widget_preview_');
$json	=json_encode($preview);

$widgets= module("holderAdmin:getWidgets");
$count	= count($widgets);
?>
<?
foreach($widgets as $widgetID => $widget){
	$name	= $widget['name'];
	if (!$name) $name = $widgetID;
	$c	= (int) $counters[$widgetID];
?>
<div class="seekLink">
    <a href="{{url:admin_holderWidgetEdit=holderName:$holderName;widgetID:$widgetID}}" id="ajax">cfg</a>
    {$c}
    <label><input type="checkbox" name="widgetDelete[]" value="{$widgetID}"/></label>
    <a href="{{url:#=holderName:$holderName;widgetAdd:$widgetID}}" title="{$widget[desc]}" class="preview" rel="{$json}">
    	{$name}
    </a>
<? if ($widget['note']){ ?>
	- {$widget[note]}
<? } ?>
</div>
<? } ?>

<? return "Используемые ($count)"; } ?>

<?
//	+function holder_tabComment
function holder_tabComment($holderName)
{
	$holders= getStorage('holder/holders', 'ini');
?>

<textarea class="input w100" name="holder[note]" rows="15">{$holders[$holderName][note]}</textarea>

<? return "Комментарий"; } ?>

