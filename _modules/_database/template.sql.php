<?
//	Построение SQL запроса по предоставленным данным
//	{select:	{fields: xxx, from: xxx, join: xxxx, where: xxx, order: xxx, group: xxx}}
//	{update:	{table: xxx, from: xxx, join: xxxx, where: xxx, values: {name:value, name: value}}}
//	{insert:	{table: xxx, from: xxx, join: xxxx, where: xxx, values: {name:value, name: value}}}
//	{delete:	{table: xxx, from: xxx, join: xxxx, where: xxx}
function module_sql(&$val, &$data)
{
	$res	= array();
	foreach($data as $queryName => &$query){
		$fn	= getFn("$sql_$queryName");
		if ($fn) $res[]	= $fn($val, $query);
	}
	return $res;
} ?>
<?
//	{select:	{fields: xxx, from: xxx, join: xxxx, where: xxx, order: xxx, group: xxx}}
function sql_select(&$val, &$query){
}?>