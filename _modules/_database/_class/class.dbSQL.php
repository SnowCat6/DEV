<?
class dbSQL
{
	static function makeRaw($db, $where, $date)
	{
		if (!is_array($where)) $where = $where?array($where):array();
		
		$join		= '';
		$thisAlias	= '';
		$table		= $db->table();
		$group		= $db->group;
	
		if ($db->fields) $fields = $db->fields;
		else $fields = '*';
	
		if ($val = $where[':from'])
		{
			unset($where[':from']);
	
			$t = array();
			foreach($val as $name => $alias)
			{
				if (!$alias) continue;
				if (is_int($name)){
					$t[]		= "$table AS $alias";
					$thisAlias	= $alias;
				}else{
					$t[]		= "$name AS $alias";
				}
			}
			$table = implode(', ', $t);
		}
		if ($val = $where[':fields']){
			unset($where[':fields']);
			$fields = $val;
		}
		if ($val = $where[':group']){
			unset($where[':group']);
			$group = $val;
		}
		if ($val = $where[':join'])
		{
			unset($where[':join']);
			foreach($val as $joinTable => $joinWhere){
				if ($joinWhere) $join  .= "INNER JOIN $joinTable ON $joinWhere ";
				else $join  .= "INNER JOIN $joinTable ";
			}
		}
		if ($db->sql)
			$where[] .= $db->sql;
			
		if ($date){
			$date		= dbEncDate($db, $date);
			$where[]	= "lastUpdate > $date";
		}
		
		$where = implode(' AND ', $where);
		
		if ($where) $where = "WHERE $where";
		if ($order = $db->order) $order = "ORDER BY $order";
		if ($group)	$group = "GROUP BY $group";
		
		//	Заменить названия полей на название с алиасом
		if ($thisAlias)
		{
			$fields	= preg_replace('#(\s|^)\*#',	"\\1$thisAlias.*",	$fields);
			
			$r = '#([\s=(]|^)(`[^`]*`)#';
			$fields	= preg_replace($r, "\\1$thisAlias.\\2" ,$fields);
			$join	= preg_replace($r, "\\1$thisAlias.\\2", $join);
			$where	= preg_replace($r, "\\1$thisAlias.\\2", $where);
			$group	= preg_replace($r, "\\1$thisAlias.\\2", $group);
			$order	= preg_replace($r, "\\1$thisAlias.\\2", $order);
		}
	
		$sql 			= array();
		$sql['action']	= 'SELECT';
		$sql['fields']	= $fields;
		$sql['from']	= $table;
		$sql['join']	= $join;
		$sql['where']	= $where;
		$sql['group']	= $group;
		$sql['order']	= $order;
		
		return $sql;
	}
};
?>