<?
class systemIO
{
	//	[key][action][path] => value....
	static function set($key, $values, $options = NULL)
	{
		$a = array('key' => $key, 'values' => $values, 'options' => $options);
//		event('systemIO.set', $a);
	}
	static function get($key, $values, $options = NULL)
	{
		$value	= array();
		$a		= array('key' => $key, 'values' => &$values, 'options' => $options);
		event('systemIO.get', $a);
		return $value;
	}
	//	Updating array by path
	static function set_data(&$data, $key, $value, $action = 'update')
	{
	}
	//	Get data value by path
	static function get_data($data, $key)
	{
		$path	= explode('.', $key);
		foreach($path as $keyName){
			if ($keyName) $data	= $data[$keyName];
		}
		return $data;
	}
};
?>