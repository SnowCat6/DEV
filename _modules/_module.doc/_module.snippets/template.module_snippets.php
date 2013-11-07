<?
function module_snippets($fn, &$data)
{
	list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("snippets_$fn");
	return $fn?$fn($val, $data):NULL;
}
function snippets_get(){
	$ini		= getCacheValue('ini');
	$snippets	= $ini[':snippets'];
	if (!is_array($snippets)) $snippets = array();

	$snippets2	= getCacheValue('localSnippets');
	if (!is_array($snippets2)) $snippets2 = array();
	
	return array_merge($snippets, $snippets2);
}
function snippets_visual($val, $data){
	return false;
}
function snippets_compile($val, &$data){
	//	[[название сниплета]] => {\{модуль}\}
	$data= preg_replace_callback('#\[\[([^\]]+)\]\]#u', 'parsePageSnippletsFn', $data);
}
function parsePageSnippletsFn($matches)
{
	$baseCode	= $matches[1];
	$ini		= getCacheValue('ini');
	$snippets	= $ini[':snippets'];
	$code		= $snippets[$baseCode];
	if ($code) return $code;

	@$snippets	= getCacheValue('localSnippets');
	return @$snippets[$baseCode];
}
function snippets_tools($val, $data){
?>
<div style="white-space:nowrap">
Сниппеты: 
<select name="snippets" id="snippets" class="input" onchange="snippetInsert('<?= htmlspecialchars($val)?>', this); ">
<option value="">-- вставить сниппет ---</option>
<?
$snippets = module('snippets:get');
foreach($snippets as $name => $code){ ?>
<option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name)?></option>
<? } ?>
</select>
</div>
<script>
function snippetInsert(name, snippet){
<? if (module('snippets:visual')){ ?>
	var code = '<p class="snippet ' + snippet.value + '">' + "</p>";
<? }else{ ?>
	var code = '[[' + snippet.value + ']]';
<? } ?>
	editorInsertHTML(name, code);
	snippet.selectedIndex = 0;
}
</script>
<? } ?>
<? function snippets_toolsPanel($val, &$data){
	if (!access('write', 'snippets:')) return;
	$data['Сниппеты#ajax']	= getURL('snippets_all');
}
function module_snippets_access($acccess, &$data){
	return hasAccessRole('admin,developer,writer');
}
?>