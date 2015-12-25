<?
function module_systemIO($fn, $data)
{
	list($fn, $val)	= explode(':', $fn, 2);
	$fn	= getFn("systemIO_$fn");
	if ($fn) return $fn($val, $data);
}

function systemIO_action($val, $data)
{
	echo getURL('admin_update', $data);
}

function systemIO_name($val, $data)
{
	$action	= $data['action'];
	if (!$action) $action = 'update';
	
	echo htmlspecialchars("systemIO[$action][$val]");
}

function systemIO_get($key, $options)
{
	echo htmlspecialchars(systemIO::get($key, $options));
}

function systemIO_default($val, $data)
{
}

function systemIO_help($val, $data)
{
}
?>

<?
function systemIO_update($val, $data)
{
	$data	= getValue('systemIO');
	if (!is_array($data)) return;
}
?>