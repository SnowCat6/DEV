<? function admin_access($access, $data)
{
	$tool	= $data[1];
	switch($tool)
	{
	case 'SEO':
		return hasAccessRole('SEO');
	case 'serverInfo':
		return hasAccessRole('developer');
	case 'settings':
		return hasAccessRole('admin,developer');
	case 'global':
		if (!hasAccessRole('developer')) return;

		$gini			= getGlobalCacheValue('ini');
		$globalAccessIP	= $gini[':']['globalAccessIP'];
		if (GetIntIP($globalAccessIP) == 0) return true;
		return $globalAccessIP == GetStringIP(userIP());
	}
	return hasAccessRole('admin,developer');
}?>