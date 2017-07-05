<?
define('feedbackRulesFolder', 'images/policy/Title/');

 function feedback_rules($val, $data)
{
	$files = getSiteFiles(feedbackRulesFolder);

	if (!$files) return false;
	list(,$path) = each($files);
	$path	= imagePath2local($path);
	
	$bOK = getValue('rulesAccept') == 1;
	if ($val) return $bOK?true:'Необходимо согласие с политикой конфиденциальности';
?>
<p>
    <label>
        <input type="checkbox" name="rulesAccept" value="1" {checked:$bOK} />
        Я согласен с <a href="{$path}">политикой конфиденциальности</a>
    </label>
</p>
<? } ?>

<?
//	+function feedback_rulesAdmin
function feedback_rulesAdmin($val, $data){?>
<?
	if (!access('write', 'feedback:')) return;

	if (getValue('feedbackDelete')){
		delTree(localRootPath . '/' . feedbackRulesFolder);
	}

	$files = getSiteFiles(feedbackRulesFolder);
	$p		= array(
		'uploadFolder' => feedbackRulesFolder
	);
?>

    <module:script:fileUpload />
    <script src="script/feedbackUpload.js"></script>
    <link rel="stylesheet" type="text/css" href="../../_gallery/_gallery.uploadFull/css/gallery.upload.css">
    
    <div class="feedbackPolicyUpload imageUploadFullPlace" rel="{$p|json}">
    Нажмите сюда для загрузки файла или петеращите файлы сюда.
    <div class="fileTitle">
    <? foreach($files as $name){ ?>
	    <div>{$name}</div>
	<? } ?>
    </div>
    </div>
    <? if ($files){ ?>
    	<p>
	       <input type="submit" name="feedbackDelete" value="Удалить" class="button" />
        </p>
    <? } ?>
<? } ?>