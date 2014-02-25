<? function script_property($val)
{
m('script:autocomplete');
//	Получить названия свойств для поиска
$props	= module("prop:name:globalSearch,globalSearch2,productSearch,productSearch2");
$names	= array_keys($props);
foreach($names as &$val) $val = htmlspecialchars($val);
$n		= implode('","', $names);
if ($n) $n = "\"$n\"";

$names	= implode(',', $names);
$props	= module("prop:value:$names");
$n2		= '';
foreach($props as $name => &$names){
	if ($n2) $n2 .= ",\r\n";
	$n2	.= "\"$name\": [\"";
	foreach($names as &$val) $val = htmlspecialchars($val);
	$n2	.= implode('","', $names);
	$n2	.= '"]';
}
$n2	= '{'."$n2".'}';
?>
<script language="javascript" type="application/javascript">
var propAutocomplete = {
	source: new Array(<?= $n?>),
	minLength : 0
};
var propAutocomplete2 = {
	source: fnAuotocomplete2,
	select: fnAuotocomplete3,
	minLength : 0
};
function fnAuotocomplete2(request, respond){
	var prop = window[aoutocompleteNow.attr("options")];
	var name = $(aoutocompleteNow.parent().parent().find(".autocomplete").get(0)).val();
	var a = <?= $n2?>;
	respond(a[name]);
};
function fnAuotocomplete3(event, ui){
	var v = this.value?this.value.split(', '):new Array();
	if (v.indexOf(ui.item.value) < 0) v.push(ui.item.value);
	ui.item.value = v.join(', ');
	$("input").blur();
}

$(function(){
	$(".autocomplete").each(function(index, element) {
		$(this)
		.autocomplete(window[$(this).attr("options")]);
    });
});
</script>
<? } ?>
