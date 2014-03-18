<? function script_property($val)
{
//	Получить названия свойств для поиска
$props	= module("prop:name:globalSearch,globalSearch2,productSearch,productSearch2");
$names	= array_keys($props);
foreach($names as &$val) $val = propertyJSencode($val);
$n		= implode('","', $names);
if ($n) $n = "\"$n\"";

$names	= implode(',', $names);
$props	= module("prop:value:$names");
$n2		= '';
foreach($props as $name => &$names){
	if ($n2) $n2 .= ",\r\n";
	$n2	.= "\"$name\": [\"";
	foreach($names as &$val) $val = propertyJSencode($val);
	$n2	.= implode('","', $names);
	$n2	.= '"]';
}
$n2	= '{'."$n2".'}';
?>
<script language="javascript" type="application/javascript">
var propertyFields = new Array();
var aoutocompleteNow = null;

var propAutocomplete = {
	source: new Array(<?= $n?>),
	minLength : 0
};
var propAutocomplete2 = {
	source: fnAuotocomplete2,
	select: fnAuotocomplete3,
	minLength : 0
};
function fnAuotocomplete2(request, callback)
{
	var prop = window[aoutocompleteNow.attr("options")];
	var name = $(aoutocompleteNow.parent().parent().find(".autocomplete").get(0)).val();
	var a = <?= $n2?>;
	var v = a[name];
	if (!v) v = propertyFields[name];
	if (v) return callback(v);

	$.ajax('{{url:property_getAjax}}', {data: $.param({names: name})})
	.done(function(data)
	{
		v = $.parseJSON(data);
		propertyFields[name]	= v[name];
		return callback(propertyFields[name]);
	});
};
function fnAuotocomplete3(event, ui){
	var v = this.value?this.value.split(', '):new Array();
	if (v.indexOf(ui.item.value) < 0) v.push(ui.item.value);
	ui.item.value = v.join(', ');
	$("input").blur();
}
function autocompleteFilter(req, responseFn, wordlist)
{
	var re = $.ui.autocomplete.escapeRegex(req.term);
	var matcher = new RegExp( "^" + re, "i" );
	var a = $.grep( wordlist, function(item,index){
		return matcher.test(item);
	});
	responseFn( a );
}

$(function(){
	$(".autocomplete").focus(function(){
		aoutocompleteNow = $(this);
		$(this).autocomplete(window[$(this).attr("options")]);
		$(this).autocomplete("search", this.value);
	});
});
</script>
<? } ?>
<? function propertyJSencode(&$val){
	$v = str_replace('"', '\\"', $val);
	$v = str_replace("'", "\\\\'", $v);
	return $v;
}?>
