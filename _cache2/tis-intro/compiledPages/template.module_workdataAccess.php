<? function module_workdataAccess($val, &$base)
{
	$d		= &$base[0];
	$data	= &$base[1];
	if (isset($data['fields']['workData']) && is_array($data['fields']['workData'])){
		$d['fields']['workData'] = $data['fields']['workData'];
	}
} ?>