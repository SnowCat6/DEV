<? function admin_info(){
	if (!hasAccessRole('admin,developer')) return;
	setTemplate('');
	phpinfo();
} ?>