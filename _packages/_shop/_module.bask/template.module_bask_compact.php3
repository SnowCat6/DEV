<?
function bask_compact($bask, $val, &$data)
{
	if ($bask){
		$s			= array();
		$s['type']	= 'product';
		$s['id']	= array_keys($bask);
		
		$cont	= 0;
		$sql	= array();
		doc_sql($sql, $s);
		
		$db = module('doc');
		$db->open($sql);
		while($data	= $db->next()){
			$count += $bask[$db->id()];
		}
	}else{
		$count = 0;
	}
	
	if ($count) $ordered = "В корзине <b>$count</b> шт.";
	else $ordered = "В корзине пусто";

	module('script:ajaxLink');
	module('page:style', 'bask.css');
?>
<div class="bask compact">
<div class="baskTitle"><a href="{{getURL:bask}}" id="ajax">Корзина:</a></div>
<div class="baskAvalible">{!$ordered}</div>
</div>
<? } ?>