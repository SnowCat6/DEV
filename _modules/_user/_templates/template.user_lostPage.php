<? function user_lostPage($val, $data){ ?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
{{page:title=Восстановление пароля}}
{push}
{{user:lostForm}}
{pop:lostForm}
{{display:message}}
{{display:lostForm}}
<? } ?>
