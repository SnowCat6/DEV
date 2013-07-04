<?php
/**
 * Оптимизаторский файл. Подключать только include_once!!! Не забываем global $aSEOData, где нужно.
 *
 * if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/d-seo.php')) {
 *   include_once($_SERVER['DOCUMENT_ROOT'] . '/d-seo.php');
 * }
 *
 * Изменяемые параметры массива $aSEOData (квадратными скобками выделены неактивные)
 * title             - title страницы
 * descr             - meta descr
 * keywr             - meta keywords
 *
 */

//Глобальные значения (по умолчанию)
  $aSEOData['title'] = '';
  $aSEOData['descr'] = '';
  $aSEOData['keywr'] = '';

//Определяем адрес (REQUEST_URI есть не всегда)
  $sSEOUrl = $_SERVER['REQUEST_URI'];
//Собственно вариации для страниц
  switch ($sSEOUrl) {
    case '/url.php':
      $aSEOData['title'] = 'Тайтл успешно подменен';
      break;
  
  }  





















//Обработка
  function changeHeadBlock ($sContent, $sRegExp, $sBlock) {
    if (preg_match($sRegExp, $sContent)) {
      return preg_replace($sRegExp, $sBlock, $sContent);
    }
    else {
      return str_replace('<head>', '<head>' . $sBlock, $sContent);
    }
  }
  if (isset($aSEOData['title']) && !empty($aSEOData['title'])) {
    $aSEOData['title'] = htmlspecialchars($aSEOData['title']);
    $sContent = changeHeadBlock($sContent, '#<title>.*</title>#siU', '<title>' . $aSEOData['title'] . '</title>');
  }
  if (isset($aSEOData['descr']) && !empty($aSEOData['descr'])) {
    $aSEOData['descr'] = htmlspecialchars($aSEOData['descr']);
    $sContent = changeHeadBlock($sContent, '#<meta[^>]+name[^>]{1,7}description[^>]*>#siU', '<meta name="description" content="' . $aSEOData['descr'] . '" />');
  }
  if (isset($aSEOData['keywr']) && !empty($aSEOData['keywr'])) {
    $aSEOData['keywr'] = htmlspecialchars($aSEOData['keywr']);
    $sContent = changeHeadBlock($sContent, '#<meta[^>]+name[^>]{1,7}keywords[^>]*>#siU', '<meta name="keywords" content="' . $aSEOData['keywr'] . '" />');
  }

  
  if (isset($aSEOData['h1']) && !empty($aSEOData['h1'])) {
    $sContent = preg_replace('#(<h1[^>]*>).*(</h1>)#siU', '$1'.$aSEOData['h1'].'$2', $sContent);
  }
  
  if (isset($aSEOData['text']) && !empty($aSEOData['text'])) {
    //$sContent = preg_replace('##siU', '', $sContent);
  }
  
  if (isset($aSEOData['text_alt']) && !empty($aSEOData['text_alt'])) {
    //$sContent = preg_replace('##siU', '', $sContent);
  }



  //DuSya Informer
  if(isset($_SERVER['X_DUSYA']) || isset($_SERVER['HTTP_X_DUSYA'])) {
  $sContent = str_replace('<head>', '<head><!--origUrl="' . $sSEOUrl . '"-->' , $sContent);
  }

     

?>
