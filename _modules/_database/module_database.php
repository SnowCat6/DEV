<?
function makeIDS($id, $separator = ',')
{
	$db	= new dbRow();
	if (!is_array($id)) $id = explode($separator, $id);
	foreach($id as $ndx => &$val)
	{
		$val = trim($val);
		if (preg_match('#^\d+$#', $val)) $val = (int)$val;
		else{
			if ($val) $val = dbEncString($db, $val);
			else unset($id[$ndx]);
		}
	}
	return implode($separator, $id);
}

function makeDateStamp($val){
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4}$)#', $val, $v)){
		list(,$d,$m,$y) = $v;
		return mktime(0, 0, 0, $m, $d, $y);
	}else
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}$)#', $val, $v)){
		list(,$d,$m,$y,$h,$i) = $v;
		return mktime($h, $i, 0, $m, $d, $y);
	}
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}):(\d{1,2}$)#', $val, $v)){
		list(,$d,$m,$y,$h,$i,$s) = $v;
		return mktime($h, $i, $s, $m, $d, $y);
	}
	return 0;
}
function dateStamp($val){
	if (!$val) return;
	return date('d.m.Y H:i', $val);
}
/**************************************/
function dbMakeField($val)
{
	return "`$val`";
}
/**************************************/
function dbEncString(&$db, &$val){
	$v	= $db->dbLink->escape_string($val);
	return "'$v'";
}
function dbDecString(&$db, &$val){
	return $val;
}
/**************************************/
function dbEncInt(&$db, &$val){
	return (int)$val;
}
function dbDecInt(&$db, &$val){
	return $val;
}
/**************************************/
function dbEncFloat(&$db, &$val){
	return (float)$val;
}
function dbDecFloat(&$db, &$val){
	return $val;
}
/**************************************/
function dbEncArray(&$db, &$val)
{
	if (is_null($val)){
		return 'NULL';
	}else{
		$v	= serialize($val);
		return dbEncString($db, $v);
	}
}
function dbDecArray(&$db, &$val){
	return unserialize($val);
}
/**************************************/
function dbEncDate(&$db, $val){
	if (!$val) return 'NULL';
	$val	= date('Y-m-d H:i:s', (float)$val);
	return	"DATE_ADD('$val', INTERVAL 0 DAY)";
}
function dbDecDate(&$db, $val){
	if (!preg_match('#(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})#', $val, $m))
		return NULL;

	$d = mktime($m[4], $m[5], $m[6], $m[2], $m[3], $m[1]);
	if ($d < 0) return NULL;

	return $d;
}
/**************************************/
function dbEncode(&$db, &$dbFields, &$data)
{
	dbCoDec($db, $dbFields, $data, false);
}
function dbDecode(&$db, &$dbFields, &$data)
{
	dbCoDec($db, $dbFields, $data, true);
}
/**************************************/
function dbCoDec(&$db, &$dbFields, &$data, $bDecode)
{
	foreach($data as $fieldName => &$val)
	{
		switch($dbFields[$fieldName])
		{
		case 'array':
			$val	= $bDecode?dbDecArray($db, $val):dbEncArray($db, $val);
			break;
		case 'datetime':
			$val	= $bDecode?dbDecDate($db, $val):dbEncDate($db, $val);
			break;
		case 'int':
		case 'tinyint':
			$val	= $bDecode?dbDecInt($db, $val):dbEncInt($db, $val);
			break;
		case 'float':
		case 'double':
			$val	= $bDecode?dbDecFloat($db, $val):dbEncFloat($db, $val);
			break;
		default:
			$val	= $bDecode?dbDecString($db, $val):dbEncString($db, $val);
			break;
		}
	}
}
?>