<?php
// Раздел настройки ЧПУ (Все пути начинаются с ведущего слеша от корневой директории)
  $aURLRewriter = array (
//'/link1'=>'/link1',
  );
//Сквозные редиректы
  $aR301SkipCheck = array (
//'/link1'=>'/link1',
  );
//Удаленные страницы
  $a410Response = array (
    '/test3',
  );
  $a404Response = array (
    '/test4',
  );
// Только замена ссылок
  $aURLRewriterOnly = array (
  );

  
  define('DUR_DEBUG', 0);                   //Включение режима отладки (вывод инфо в конце исходного текста на странице)
  define('DUR_PREPEND_APPEND', 0);          //Единая точка входа (.htaccess) Не рекомендуется
  define('DUR_BASE_ROOT', 0);               //Прописать принудительно <base href="http://domain.com/"> Бывает полезно при ссылках вида href="?page=2". При указании строки, пропишет ее
  define('DUR_LINK_PARAM', 0);              //Дописать путь перед ссылками вида href="?page=2"
  define('DUR_ANC_HREF', 0);                //Пофиксить ссылки вида href="#ancor"
  define('DUR_ROOT_HREF', 1);               //Пофиксить ссылки вида href="./"
  define('DUR_REGISTER_GLOBALS', 0);        //Регистрировать глобальные переменные
  define('DUR_SKIP_POST', 1);               //Не выполнять подмену при запросе POST
  define('DUR_CMS_TYPE', 'NONE');           //Включение особенностей для CMS, возможные значения: NONE, NETCAT, JOOMLA, HTML, DRUPAL, WEBASYST, ICMS
  define('DUR_OUTPUT_COMPRESS', 'AUTO');    //Сжатие выходного потока, возможные значения: NONE, GZIP, DEFLATE, AUTO, SKIP
  define('DUR_SUBDOMAINS', 0);              //Обрабатывать поддомены, указываем здесь основной домен!
  define('DUR_SKIP_USERAGENT', '#^(|mirror)$#'); //Не выполнять редиректы при указанном HTTP_USER_AGENT (регулярка)
  define('DUR_SKIP_URLS', '#^/_?(admin|manag|bitrix|indy|cms|phpshop|varvara.php|captcha|jscripts/|modules|includes|templates)#siU');  //Skip URLS
  define('DUR_FIX_CONTLEN', 1);             //Фиксить Content-Length
  define('DUR_PATHINFO', 0);                //Регистрировать переменные для передачи вида /index.php/uri

///new
  define('DUR_FIX_RELATIVE', 1);            //Фиксить относительные ссылки (только для DUR_MAIN_CYCLE = ortodox)
  define('DUR_FIX_DOTTED', 0);              //Фиксить ссылки от "./" (только для DUR_MAIN_CYCLE = ortodox)
  define('DUR_FIX_HTTP_HOST', 'xn----8sbknewdlutl.xn--p1ai');           //Фиксить HTTP_HOSTв ссылках, прописываем, например, значение "www.mysite.ru", чтобы сократить количество host-зависимых подмен ссылок
  define('DUR_CACHE_REWRITED', 0);          //Кэшировать все замены в этом рерайтере, должна быть создана папка d-cache в корне с правами на запись
  define('DUR_CACHE_MEMORY', 40960000);            //Критическая масса кеша (в байтах), при превышении этого значения кеш очищается
  define('DUR_CACHE_TIME', 3);              //Критическое время жизни кеша, при превышении этого значения кеш очищается
  define('DUR_MAIN_CYCLE', 'callback');    //Константа для выбора типа основного цикла, значения: callback, str_replace, ortodox
  define('DUR_CALLBACK_AMP', '0');    //Константа для обработки ссылок с &amp; при типе основного цикла callback





















// Раздел обработки
  define('DUR_TIME_START', microtime(true));
  define('DUR_REQUEST_URI', $_SERVER['REQUEST_URI']);
  define('DUR_HTTP_HOST', $_SERVER['HTTP_HOST']);
  define('DUR_FULL_URI', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  define('BX_COMPRESSION_DISABLED', true); //Hack for bitrix
    if (preg_match(DUR_SKIP_URLS, DUR_REQUEST_URI, $aM) || (isset ($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != DUR_FIX_HTTP_HOST))
    define('DUR_SKIP_THIS', true);
  else
    define('DUR_SKIP_THIS', false);
  define('DUR_SKIP_R301', !isset($_SERVER['HTTP_USER_AGENT']) || preg_match(DUR_SKIP_USERAGENT, $_SERVER['HTTP_USER_AGENT']));
  if (defined('DUR_DEBUG') && DUR_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('error_reportings', E_ALL);
  }
  if (isset($_GET['_openstat'])) {
    unset($_GET['_openstat']);
    unset($_REQUEST['_openstat']);
    unset($HTTP_GET_VARS['_openstat']);
    $_SERVER['REQUEST_URI'] = preg_replace('%[&?]_openstat=[^&]+(&|$)%siU', '', $_SERVER['REQUEST_URI']);
  }
  if (isset($a410Response[$_SERVER['REQUEST_URI']]) && !DUR_SKIP_THIS) {
    header('HTTP/1.0 410 Gone');
    echo '<h1 style="font-size: 18pt;">Ошибка 410</h1><p>Страница удалена</p><p style="text-align: right; margin: 10px;"><a href="/">На главную</a></p>';
    exit;
  }
  if (in_array($_SERVER['REQUEST_URI'], $a404Response) && !DUR_SKIP_THIS) {
    dur404native();
  }
  if (isset($aR301SkipCheck[$_SERVER['REQUEST_URI']]) && !DUR_SKIP_THIS && !DUR_SKIP_R301) {
    if (!defined('DUR_SKIP_POST') || !DUR_SKIP_POST || (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST')) {
      header('Location: ' . $aR301SkipCheck[$_SERVER['REQUEST_URI']], true, 301);
      exit;
    }
  }
  foreach ($aURLRewriter as $sKey => $sVal) {
    $aURLRewriter[$sKey] = str_replace(
      array('р', 'у', 'к', 'е', 'н', 'х', 'в', 'а', 'о', 'ч', 'с', 'м', 'и', 'т', ' '),
      array('p', 'y', 'k', 'e', 'h', 'x', 'b', 'a', 'o', '4', 'c', 'm', 'n', 't', '_'),
      $sVal
    );
    if (!defined('DUR_SEO_REQUEST_URI') && ($sVal == $_SERVER['REQUEST_URI'])) {
      define('DUR_SEO_REQUEST_URI', $sKey);
    }
  }
  $aURFlip = array_flip($aURLRewriter);
  //Многократная вложенность замен (до 10)
  for ($i = 0; $i < 10; $i++) {
    foreach ($aURLRewriter as $sFrom => $sTo) {
      if (isset($aURLRewriter[$sTo])) {
        $aURLRewriter[$sFrom] = $aURLRewriter[$sTo];
        $aURFlip[$aURLRewriter[$sTo]] = $sFrom;
      }
    }
  }
  //Joomla hack! (Против защиты от register globals)
  if (defined('DUR_CMS_TYPE') && (DUR_CMS_TYPE == 'JOOMLA')) {
    $_SERVER['dur'] = array($aURLRewriter, $aURFlip, $aURLRewriterOnly);
  }
  //Единая точка входа
  if (defined('DUR_PREPEND_APPEND') && DUR_PREPEND_APPEND && !DUR_SKIP_THIS) {
    durRun ();
  }


// Функции
  function durRun () {
    if (defined('DUR_RUNNED')) return;
//    if (isset())
    define('DUR_RUNNED', 1);
    durR301();
    ob_start('durLinkChanger');
    durIFRewrite();
  }

  function dur404 () {
    $aPages404 = array('404.php', '404.html', '404.htm', 'index.php', 'index.html', 'index.htm');
    header('HTTP/1.1 404 Not found');
    foreach ($aPages404 as $sPage404) {
      if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $sPage404)) {
        include($_SERVER['DOCUMENT_ROOT'] . '/' . $sPage404);
        exit;
      }
    }
    echo '<h1>Ошибка 404</h1><p>Страница не найдена</p><p style="text-align: right; margin: 10px;"><a href="/">На главную</a></p>';
    exit;
  }
  function dur404native() {
	$_SERVER['REQUEST_URI'] = '/thispagewasdeleted';
	$_GET = $_REQUEST = array(); 
    }
  function durRewrite ($sURL) {
    global $QUERY_STRING, $REQUEST_URI, $REDIRECT_URL, $HTTP_GET_VARS;
    define('DUR_DEBUG_BEFORE', "SERVER:\n" . durDebugVar($_SERVER) . "\n\nGET:\n" . durDebugVar($_GET) . "\n\nREQUEST:\n" . durDebugVar($_REQUEST) . "\n");
    if (defined('DUR_CMS_TYPE') && (DUR_CMS_TYPE == 'WEBASYST')) {
      $sURL = '/?__furl_path=' . substr($sURL, 1) . '&frontend=1';
    }
    if (defined('DUR_CMS_TYPE') && (DUR_CMS_TYPE == 'ICMS')) {
      $sURL = '/index.php?path=' . substr($sURL, 1, -5) . '&frontend=1';
    }
    $QUERY_STRING = strpos($sURL, '?') ? substr($sURL, strpos($sURL, '?') + 1) : '';
    $REQUEST_URI = $sURL;
    $REDIRECT_URL = $sURL;
    $_SERVER['QUERY_STRING'] = $QUERY_STRING;
    $_SERVER['REDIRECT_URL'] = $sURL;
    $_SERVER['REQUEST_URI'] = $sURL;
    if (defined('DUR_CMS_TYPE') && (DUR_CMS_TYPE == 'NETCAT')) {
      putenv('REQUEST_URI=' . $sURL);
    }
    if (defined('DUR_CMS_TYPE') && (DUR_CMS_TYPE == 'DRUPAL')) {
      $_GET['q'] = substr($sURL, 1);
      $_REQUEST['q'] = substr($sURL, 1);
    }
    if (preg_match_all('%[\?&]([^\=]+)\=([^&]*)%', $sURL, $aM)) {
      $aParams = array();
      foreach ($aM[1] as $iKey => $sName) {
        $sVal = urldecode($aM[2][$iKey]);
        if (preg_match('#^(.+)\[\]$#siU', $sName, $aMatch)) {
          $aParams[$aMatch[1]][] = $sVal;
        }
        elseif (preg_match('#^(.+)\[([\w-]+)\]$#siU', $sName, $aMatch)) {
          $aParams[$aMatch[1]][$aMatch[2]] = $sVal;
        }
        else {
          $aParams[$sName] = $sVal;
        }
      }
      foreach ($aParams as $sKey => $mVal) {
        $_GET[$sKey] = $mVal;
        $HTTP_GET_VARS[$sKey] = $mVal;
        $_REQUEST[$sKey] = $mVal;
        if (defined('DUR_REGISTER_GLOBALS') && DUR_REGISTER_GLOBALS) {
          global $$sKey;
          $$sKey = $mVal;
        }
      }
    }
    if (defined('DUR_PATHINFO') && DUR_PATHINFO) {
      $_SERVER['PATH_INFO'] = substr($sURL, 1);
      $_SERVER['PHP_SELF'] = $sURL;
    }
    if (DUR_CMS_TYPE == 'HTML') {
      $sFName = $sURL;
      if ($iPos = strpos($sFName, '?')) {
        $sFName = substr($sFName, 0, $iPos);
      }
      if (file_exists($_SERVER['DOCUMENT_ROOT'] . $sFName)) {
        include($_SERVER['DOCUMENT_ROOT'] . $sFName);
        exit;
      }
      else {
        dur404();
      }
    }
  }

  function durIFRewrite () {
    global $aURFlip, $aURLRewriter;
    if (DUR_SKIP_THIS) return;
    $sKey = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    if (defined('DUR_SUBDOMAINS') && DUR_SUBDOMAINS && isset($aURFlip[$sKey])) {
      if (!defined('DUR_ORIG_RURI')) {
        define('DUR_ORIG_RURI', $aURFlip[$sKey]);
      }
      durRewrite ($aURFlip[$sKey]);
    }
    elseif (isset($aURFlip[$_SERVER['REQUEST_URI']])) {
      if (!defined('DUR_ORIG_RURI')) {
        define('DUR_ORIG_RURI', $aURFlip[$_SERVER['REQUEST_URI']]);
      }
      durRewrite ($aURFlip[$_SERVER['REQUEST_URI']]);
    }
    elseif (defined('DUR_CMS_TYPE') && (DUR_CMS_TYPE == 'HTML')) {
      if (file_exists($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'])) {
        durRewrite ($_SERVER['REQUEST_URI']);
      }
      else {
        dur404();
      }
    }
  }

  function durR301 () {
    global $aURFlip, $aURLRewriter;
    if (DUR_SKIP_THIS || DUR_SKIP_R301) return;
    if (defined('DUR_SKIP_POST') && DUR_SKIP_POST && (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')) {
      return;
    }
    if (isset($aURLRewriter[$_SERVER['REQUEST_URI']])) {
      if ('http://' . DUR_HTTP_HOST == trim($aURLRewriter[$_SERVER['REQUEST_URI']], '/')) {
        return;
      }
      header('Location: ' . $aURLRewriter[$_SERVER['REQUEST_URI']], true, 301);
      exit;
    }
  }

  function durRExpEscape ($sStr) {
    return str_replace(array('?', '.', '-', ':', '%', '[', ']', '(', ')'), array('\\?', '\\.', '\\-', '\\:', '\\%', '\\[', '\\]', '\\(', '\\)'), $sStr);
  }

  function durReplaceOnceLink ($sLink, $sNewLink, $sContent) {
    $sContent = preg_replace('%(href\s*=\s*[\'"]?)\s*' . durRExpEscape ($sLink) . '([#\'"\s>])%siU', '$1' . $sNewLink . '$2', $sContent);
    if (strpos($sLink,'&'))
      $sContent = preg_replace('%(href\s*=\s*[\'"]?)\s*' . durRExpEscape (str_replace('&', '&amp;', $sLink)) . '([#\'"\s>])%siU', '$1' . $sNewLink . '$2', $sContent);
    return $sContent;
  }

  function durReplaceLink ($sHost, $sBase, $sFrom, $sTo, $sContent) {
    $sNewLink = $sTo;
//  Link type: "http://domain/link"
    $sContent = durReplaceOnceLink ('http://' . $sHost . $sFrom, $sNewLink, $sContent);
//  Link type: "https://domain.com/link"
//     $sContent = durReplaceOnceLink ('https://' . $sHost . $sFrom, $sNewLink, $sContent);
//  Link type: "//domain.com/link"
//     $sContent = durReplaceOnceLink ('//' . $sHost . $sFrom, $sNewLink, $sContent);
    if (!defined('DUR_FIRST_TIC'))
      {
//    Link type: "/link"
      $sContent = durReplaceOnceLink ($sFrom, $sNewLink, $sContent);
//    Link type: "./link"
      if (defined('DUR_FIX_DOTTED') && DUR_FIX_DOTTED) {
        $sContent = durReplaceOnceLink ('.' . $sFrom, $sNewLink, $sContent);
      }
      if (defined('DUR_FIX_RELATIVE') && DUR_FIX_RELATIVE) {
      // Link type: "link" (Calc fromlink)
	$aLink = explode('/', $sFrom);
	$aBase = empty($sBase) ? array('') : explode('/', str_replace('//', '/', '/' . $sBase));
	$sReplLnk = '';
	for ($i = 0; $i < max(count($aLink), count($aBase)); $i++) {
	  if (isset($aBase[$i]) && isset($aLink[$i])) {
	    if ($aLink[$i] == $aBase[$i]) {
	      continue;
	    }
	    else {
	      for ($j = $i; $j < count($aBase); $j++) {
		$sReplLnk .= '../';
	      }
	      for ($j = $i; $j < count($aLink); $j++) {
		$sReplLnk .= $aLink[$j] . '/';
	      }
	      break;
	    }
	  }
	  elseif (isset($aLink[$i])) {
	    $sReplLnk .= $aLink[$i] . '/';
	  }
	  elseif (isset($aBase[$i])) {
	    $sReplLnk .= '../';
	  }
	}
	$sReplLnk = preg_replace('%/+%', '/', $sReplLnk);
	$sReplLnk2 = trim($sReplLnk, '/');
	$sReplLnk3 = rtrim($sReplLnk2, '.');
	if (strlen($sReplLnk) > 1) {
	  $sContent = durReplaceOnceLink ($sReplLnk, $sNewLink, $sContent);
	  if (defined('DUR_FIX_DOTTED') && DUR_FIX_DOTTED) {
	    $sContent = durReplaceOnceLink ('./' . $sReplLnk, $sNewLink, $sContent);
	  }
	}
	if (($sReplLnk2 != $sReplLnk) && (strlen($sReplLnk2) > 1)) {
	  $sContent = durReplaceOnceLink ($sReplLnk2, $sNewLink, $sContent);
	  if (defined('DUR_FIX_DOTTED') && DUR_FIX_DOTTED) {
            $sContent = durReplaceOnceLink ('./' . $sReplLnk2, $sNewLink, $sContent);
	  }
	}
	if (($sReplLnk3 != $sReplLnk2) && (strlen($sReplLnk3) > 1)) {
	  $sContent = durReplaceOnceLink ($sReplLnk3, $sNewLink, $sContent);
	  if (defined('DUR_FIX_DOTTED') && DUR_FIX_DOTTED) {
            $sContent = durReplaceOnceLink ('./' . $sReplLnk3, $sNewLink, $sContent);
	  }
	}
      }
    }
    return $sContent;
  }

  function durGZDecode($sS) {
    $sM = ord(substr($sS,2,1)); $iF = ord(substr($sS,3,1));
    if ($iF & 31 != $iF) return null;
    $iLH = 10; $iLE = 0;
    if ($iF & 4) {
      if ($iL - $iLH - 2 < 8) return false;
      $iLE = unpack('v',substr($sS,8,2));
      $iLE = $iLE[1];
      if ($iL - $iLH - 2 - $iLE < 8) return false;
      $iLH += 2 + $iLE;
    }
    $iFCN = $iFNL = 0;
    if ($iF & 8) {
      if ($iL - $iLH - 1 < 8) return false;
      $iFNL = strpos(substr($sS,8+$iLE),chr(0));
      if ($iFNL === false || $iL - $iLH - $iFNL - 1 < 8) return false;
      $iLH += $iFNL + 1;
    }
    if ($iF & 16) {
      if ($iL - $iLH - 1 < 8) return false;
      $iFCN = strpos(substr($sS,8+$iLE+$iFNL),chr(0));
      if ($iFCN === false || $iL - $iLH - $iFCN - 1 < 8) return false;
      $iLH += $iFCN + 1;
    }
    $sHCRC = '';
    if ($iF & 2) {
      if ($iL - $iLH - 2 < 8) return false;
      $calccrc = crc32(substr($sS,0,$iLH)) & 0xffff;
      $sHCRC = unpack('v', substr($sS,$iLH,2));
      $sHCRC = $sHCRC[1];
      if ($sHCRC != $calccrc) return false;
      $iLH += 2;
    }
    $sScrc = unpack('V',substr($sS,-8,4));
    $sScrc = $sScrc[1];
    $iSZ = unpack('V',substr($sS,-4));
    $iSZ = $iSZ[1];
    $iLBD = $iL-$iLH-8;
    if ($iLBD < 1) return null;
    $sB = substr($sS,$iLH,$iLBD);
    $sS = '';
    if ($iLBD > 0) {
      if ($sM == 8) $sS = gzinflate($sB);
      else return false;
    }
    if ($iSZ != strlen($sS) || crc32($sS) != $sScrc) return false;
    return $sS;
  }

  function durGZDecode2($sS) {
    $iLen = strlen($sS);
    $sDigits = substr($sS, 0, 2);
    $iMethod = ord(substr($sS, 2, 1));
    $iFlags  = ord(substr($sS, 3, 1));
    if ($iFlags & 31 != $iFlags) return false;
    $aMtime = unpack('V', substr($sS, 4, 4));
    $iMtime = $aMtime[1];
    $sXFL   = substr($sS, 8, 1);
    $sOS    = substr($sS, 8, 1);
    $iHeaderLen = 10;
    $iExtraLen  = 0;
    $sExtra     = '';
    if ($iFlags & 4) {
      if ($iLen - $iHeaderLen - 2 < 8) return false;
      $iExtraLen = unpack('v', substr($sS, 8, 2));
      $iExtraLen = $iExtraLen[1];
      if ($iLen - $iHeaderLen - 2 - $iExtraLen < 8) return false;
      $sExtra = substr($sS, 10, $iExtraLen);
      $iHeaderLen += 2 + $iExtraLen;
    }
    $iFilenameLen = 0;
    $sFilename = '';
    if ($iFlags & 8) {
      if ($iLen - $iHeaderLen - 1 < 8) return false;
      $iFilenameLen = strpos(substr($sS, $iHeaderLen), chr(0));
      if ($iFilenameLen === false || $iLen - $iHeaderLen - $iFilenameLen - 1 < 8) return false;
      $sFilename = substr($sS, $iHeaderLen, $iFilenameLen);
      $iHeaderLen += $iFilenameLen + 1;
    }
    $iCommentLen = 0;
    $sComment = '';
    if ($iFlags & 16) {
      if ($iLen - $iHeaderLen - 1 < 8) return false;
      $iCommentLen = strpos(substr($sS, $iHeaderLen), chr(0));
      if ($iCommentLen === false || $iLen - $iHeaderLen - $iCommentLen - 1 < 8) return false;
      $sComment = substr($sS, $iHeaderLen, $iCommentLen);
      $iHeaderLen += $iCommentLen + 1;
    }
    $sCRC = '';
    if ($iFlags & 2) {
      if ($iLen - $iHeaderLen - 2 < 8) return false;
      $sCalcCRC = crc32(substr($sS, 0, $iHeaderLen)) & 0xffff;
      $sCRC = unpack('v', substr($sS, $iHeaderLen, 2));
      $sCRC = $sCRC[1];
      if ($sCRC != $sCalcCRC) return false;
      $iHeaderLen += 2;
    }
    $sDataCRC = unpack('V', substr($sS, -8, 4));
    $sDataCRC = sprintf('%u', $sDataCRC[1] & 0xFFFFFFFF);
    $iSize = unpack('V', substr($sS, -4));
    $iSize = $iSize[1];
    $iBodyLen = $iLen - $iHeaderLen - 8;
    if ($iBodyLen < 1) return false;
    $sBody = substr($sS, $iHeaderLen, $iBodyLen);
    $sS = '';
    if ($iBodyLen > 0) {
      switch ($iMethod) {
        case 8: $sS = gzinflate($sBody); break;
        default: return false;
      }
    }
    $sCRC  = sprintf('%u', crc32($sS));
    $bCRCOK = ($sCRC == $sDataCRC);
    $bLenOK = ($iSize == strlen($sS));
    if (!$bLenOK || !$bCRCOK) return false;
    return $sS;
  }

  function durGZCheck ($sContent) {
    $iLen = strlen($sContent);
    if ($iLen < 18 || strcmp(substr($sContent, 0, 2), "\x1f\x8b")) {
      return $sContent;
    }
    $sData = durGZDecode2($sContent);
    if (!$sData) {
      $sData = durGZDecode($sContent);
    }
    return $sData ? $sData : $sContent;
  }

  function durOutputCompress ($sContent) {
    if (!defined('DUR_OUTPUT_COMPRESS')) {
      define('DUR_OUTPUT_COMPRESS', 'SKIP');
    }
    if (DUR_OUTPUT_COMPRESS == 'SKIP') {
      return $sContent;
    }
    $aAccept = array();
    if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
      $aAccept = array_map('trim', explode(',', strtolower($_SERVER['HTTP_ACCEPT_ENCODING'])));
    }
    $bGZIP = in_array('gzip', $aAccept) && function_exists('gzencode');
    $bDEFL = in_array('deflate', $aAccept) && function_exists('gzdeflate');
    $sCompress = DUR_OUTPUT_COMPRESS;
    if ((!$bGZIP && !$bDEFL) || (!$bGZIP && ($sCompress == 'GZIP')) || (!$bDEFL && ($sCompress == 'DEFLATE'))) {
      $sCompress = 'NONE';
    }
    if ($sCompress == 'AUTO') {
      $sCompress = $bGZIP ? 'GZIP' : ($bDEFL ? 'DEFLATE' : 'NONE');
    }
    switch ($sCompress) {
      case 'GZIP':
        header('Content-Encoding: gzip');
        $sContent = gzencode($sContent);
        break;
      case 'DEFLATE':
        header('Content-Encoding: deflate');
        $sContent = gzdeflate($sContent, 9);
        break;
      default:
        //header('Content-Encoding: none');
    }
    return $sContent;
  }

  function durDebugEscape ($sText) {
    return str_replace(array('--', '-->'), array('==', '==}'), $sText);
  }

  function durDebugVar ($mVar, $sPref = '  ') {
    $Ret = '';
    foreach ($mVar as $sKey => $sVal) {
      $Ret .= "{$sPref}{$sKey} => ";
      if (is_array($sVal)) {
        $Ret .= "ARRAY (\n" . durDebugVar($sVal, $sPref.'  ') . "{$sPref})\n";
      }
      else {
        $Ret .= "{$sVal}\n";
      }
    }
    return durDebugEscape($Ret);
  }




 function durLinkChanger ($sContent) {
    global $aURFlip, $aURLRewriter, $aURLRewriterOnly;

    if (DUR_SKIP_THIS) return $sContent;
    if (strlen($sContent) < 500) return $sContent;
    if (DUR_CACHE_REWRITED && file_exists($_SERVER['DOCUMENT_ROOT'].'/d-cache')) {
    
    /// Модуль кеширования контента - start
    
      $aDataStore = array();
      
      $icachedays = DUR_CACHE_TIME*60*60*24;
      
      $sMD5Content = md5($sContent); // MD5 от контента составляет часть имени файла кэша; это значение нужно для поиска готового к выводу контента в кеше
      $sCacheFName = $_SERVER['DOCUMENT_ROOT'].'/d-cache/'.$sMD5Content.'.html.cache'; // имя файла кеша текущего контента
      $sTimeFName = $_SERVER['DOCUMENT_ROOT'].'/d-cache/time.cache'; // имя файла данных кеша
	
      $aStoredData = array();
      $aStoredData = unserialize(file_get_contents($sTimeFName)); // массив из файла данных кеша
      $timestamp = $aStoredData['d_scripts_time']; // время последнего изменения файлов d-seo и d-url-rewriter
      $tOverallLenght = $aStoredData['cache_weight']; // Занимаемое кешем место на диске
      $tLastClear = $aStoredData['last_clear_time'];
      

      
      $dtimestamp = filemtime($_SERVER['DOCUMENT_ROOT'].'/d-url-rewriter.php'); // время изменения d-url-rewriter

 // если есть файл d-seo, то берем его время изменения и записываем в переменную $dtimestamp максимум от времени последнего изменения скриптов
      if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/d-seo.php')) {
	    $dTMPtimestamp = filemtime($_SERVER['DOCUMENT_ROOT'].'/d-seo.php');
	    if ($dTMPtimestamp > $dtimestamp) 
		  $dtimestamp = $dTMPtimestamp;
      }
      
      
      
 // если время последнего изменения, записанное в файл данных кеша отличается от реального, чистим весь кеш.
      if ($timestamp != $dtimestamp || $tOverallLenght > DUR_CACHE_MEMORY || time() - $tLastClear > $icachedays) {
	if($dh = @opendir($_SERVER['DOCUMENT_ROOT'].'/d-cache/')) {
	  while (($obj = readdir($dh)) !== false) {
	    if($obj=='.' || $obj=='..') continue;
	    @unlink($_SERVER['DOCUMENT_ROOT'].'/d-cache/'.$obj);
	  }
	  closedir($dh);
	}
	$aDataToStore['d_scripts_time'] = $dtimestamp; // записываем в массив время последнего изменения файлов d-seo и d-url-rewriter
 	$aDataToStore['last_clear_time'] = $tLastClear = time();
 	$aDataToStore['cache_weight'] = $tOverallLenght = 0;
	file_put_contents($sTimeFName,serialize($aDataToStore));
      }
      }
 // если есть соотв. файл в кеше, записываем его содержимое в $sContent
	if (DUR_CACHE_REWRITED && isset ($sCacheFName) && file_exists($sCacheFName)) {
	    $sContent = file_get_contents($sCacheFName);
	}
	
    /// Модуль кеширования контента - break
    
    else {
    
    $iTimeStart = microtime(true);
    $sContent = durGZCheck($sContent);
    if (defined('DUR_CMS_TYPE') && (DUR_CMS_TYPE == 'JOOMLA') && isset($_SERVER['dur'])) {
      $aURLRewriter = $_SERVER['dur'][0];
      $aURFlip = $_SERVER['dur'][1];
      $aURLRewriterOnly = $_SERVER['dur'][2];
      unset($_SERVER['dur']);
    }
    $aURLRewriter = array_merge($aURLRewriter, $aURLRewriterOnly);
    //Base path
    if (preg_match('%<[^<>]*base[^<>]*href=[\'"]?([\w_\-\.\:/]+)[\'"\s>][^<>]*>%siU', $sContent, $aM)) {
      $sBase = $aM[1];
      $sBaseHref = $aM[1]; 
    }
    else {
      $sBase = 'http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/'));
      $sBaseHref = ''; 
    }
    $sBase = trim(str_replace(array('http://', 'https://'), '', $sBase), '/');
    $aHosts = array($_SERVER['HTTP_HOST']);
    if (substr($_SERVER['HTTP_HOST'], 0, 4) == 'www.') {
      $aHosts[] = substr($_SERVER['HTTP_HOST'], 4);
    }
    if (defined('DUR_SUBDOMAINS') && DUR_SUBDOMAINS) {
      $sExtHost = str_replace('www.www.', 'www.', 'www.' . DUR_SUBDOMAINS);
      $aHosts[] = $sExtHost;
      $aHosts[] = str_replace('www.', '', $sExtHost);
    }
    $aHosts = array_unique($aHosts);
    $sBase = str_replace($aHosts, '', $sBase);
    
    //href="?..."
    if (defined('DUR_LINK_PARAM') &&  DUR_LINK_PARAM) {
      if (defined('DUR_ORIG_RURI'))
	$sContent = preg_replace('%(href\s*=\s*[\'"]?)\s*([?#].*[#\'"\s>])%siU', '$1' . DUR_ORIG_RURI . '$2', $sContent);
      else
	{
	$sOrigUri = preg_replace('#^(.*)\?.*$#','$1',$_SERVER['REQUEST_URI']);
	$sContent = preg_replace('%(href\s*=\s*[\'"]?)\s*([?#].*[#\'"\s>])%siU', '$1' . $sOrigUri . '$2', $sContent);
	}
    }
//     $sContent .= '|'.DUR_ORIG_RURI.'|';
    
    if (defined('DUR_FIX_HTTP_HOST') && DUR_FIX_HTTP_HOST) {
      $aHosts = array(DUR_FIX_HTTP_HOST);
      $sFalseHost = str_replace('www.www.','','www.'.DUR_FIX_HTTP_HOST);
      $sContent = str_replace('http://'.$sFalseHost, 'http://'.DUR_FIX_HTTP_HOST, $sContent);
      
    }
   
//Main cicle

  if (defined('DUR_MAIN_CYCLE'))
    {
    if (DUR_MAIN_CYCLE == 'str_replace' || DUR_MAIN_CYCLE == 'callback') 
      {
///Нормализация ссылок, все ссылки на сайте должны прийти к виду href="http://HTTP_HOST/REQUEST_URI"   , т.е. в кавычках, без пробелов и с хостом
///Опасносте начинается здесь
      $sContent = preg_replace('#href\s*=[\s]?"\s*([A-Za-z0-9\?\/][^"]*)"#siU','href="http://'.DUR_FIX_HTTP_HOST.'/$1"',$sContent);
      $sContent = preg_replace('#href\s*=[\s]?([A-Za-z0-9\?\/][^\s]*)(\s|/>|>)#siU','href="http://'.DUR_FIX_HTTP_HOST.'/$1"$2',$sContent);
      $sContent = str_replace(
	array('http://'.DUR_FIX_HTTP_HOST.'/https://','http://'.DUR_FIX_HTTP_HOST.'/http://','http://'.DUR_FIX_HTTP_HOST.'//','http://'.DUR_FIX_HTTP_HOST.'/mailto:','http://'.DUR_FIX_HTTP_HOST.'/icq:','http://'.DUR_FIX_HTTP_HOST.'/javascript:'),
	array('https://','http://','http://'.DUR_FIX_HTTP_HOST.'/','mailto:','icq:', 'javascript:'),
	$sContent
	);
/// - end  
      }
    if (DUR_MAIN_CYCLE == 'str_replace')
      {$sContent = str_replace('#','"#placeholder#',$sContent);
	foreach ($aURLRewriter as $sFrom => $sTo) {
	  if (strpos($sContent, 'href="http://'.DUR_FIX_HTTP_HOST.$sFrom.'"')) 
	    $sContent = str_replace('href="http://'.DUR_FIX_HTTP_HOST.$sFrom.'"','href="http://'.DUR_FIX_HTTP_HOST.$sTo.'"',$sContent);
	$sContent = str_replace('"#placeholder#','#',$sContent);
	}
      }
    else if(DUR_MAIN_CYCLE == 'callback')  
      { 
	function durMainCycleCallback($href) {
	  global $aURLRewriter;
	  if (isset ($href[1]))
	    {
	    if (defined('DUR_CALLBACK_AMP') && DUR_CALLBACK_AMP == 1)
	      if (strpos($href[1], '&amp;','&')) 
		$href[1] = str_replace('&amp;','&',$href[1]);
	    if (isset($aURLRewriter[$href[1]]) && $aURLRewriter[$href[1]])
	    return 'href="http://'
		    .DUR_FIX_HTTP_HOST
		    .$aURLRewriter[$href[1]]
		    .'"';
	    else return $href[0];
	  }
	}
	
      $sContent = str_replace('#','"#placeholder#',$sContent);
      $sContent = preg_replace_callback('#href="http://'.DUR_FIX_HTTP_HOST.'([^"]*)"#siU','durMainCycleCallback',$sContent);
      $sContent = str_replace('"#placeholder#','#',$sContent);
      }
    else if(DUR_MAIN_CYCLE == 'ortodox')  
      {
	foreach ($aHosts as $sHost) {
	  foreach ($aURLRewriter as $sFrom => $sTo) {
	    $sContent = durReplaceLink ($sHost, $sBase, $sFrom, $sTo, $sContent);
	  }
	if (!defined("DUR_FIRST_TIC")) define("DUR_FIRST_TIC", true);
	}
      }
    else {$sContent .= "<!--Nothing to do here!-->";}
    }

    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/d-seo.php')) {
      include_once($_SERVER['DOCUMENT_ROOT'] . '/d-seo.php');
    }
    if ((defined('DUR_BASE_ROOT') && DUR_BASE_ROOT) || !empty($sBaseHref)) {
      if (strlen(DUR_BASE_ROOT) > 7) {
        $sBaseHref = DUR_BASE_ROOT;
      }
      else {
        $sBaseHref = (empty($sBaseHref) ? 'http://' . $aHosts[0] : $sBaseHref) . '/';
      }
      $sBaseHref = trim($sBaseHref, '/') . '/';
      $sBaseHref = '<base href="' . $sBaseHref . '">';
      $sContent = preg_replace('%<base[^>]+href[^>]+>%siU', '', $sContent);
      $sContent = preg_replace('%(<head[^>]*>)%siU', "$1" . $sBaseHref, $sContent);
    }
    if (defined('DUR_ANC_HREF') && DUR_ANC_HREF) {
      $sContent = preg_replace('%(href\s*=\s*["\']+)(#\w)%siU', '$1' . DUR_REQUEST_URI . '$2', $sContent);
    }
    if (defined('DUR_ROOT_HREF') && DUR_ROOT_HREF) {
      $sContent = preg_replace('%(href\s*=\s*["\']*)\./%siU', '$1http://' . $_SERVER['HTTP_HOST'] . $sBase . '/', $sContent);
    }
    if (function_exists('durOtherReplacer')) {
      $sContent = durOtherReplacer ($sContent);
    }
    }
    
    if (DUR_CACHE_REWRITED && file_exists($_SERVER['DOCUMENT_ROOT'].'/d-cache')) {
      /// Модуль кеширования контента - continue
      file_put_contents($sCacheFName, $sContent);
//       $aDataToStore = $aStoredData;
      $aDataToStore['last_clear_time'] = $tLastClear;
      $aDataToStore['d_scripts_time'] = $dtimestamp;
      $aDataToStore['cache_weight'] = (int)$tOverallLenght + (int)strlen($sContent);
      file_put_contents($sTimeFName,serialize($aDataToStore));
      /// Модуль кеширования контента - end   
    }
    
    
    if (defined('DUR_DEBUG') && DUR_DEBUG) {
      $sContent .= "\n<!--\n";
      if (defined('DUR_DEBUG_BEFORE') && DUR_DEBUG_BEFORE) {
        $sContent .= " ===== VARS BEFORE REWRITE =====\n\n" . DUR_DEBUG_BEFORE;
      }
      $sContent .= "===== VARS AFTER REWRITE =====\n\nSERVER:\n" . durDebugVar($_SERVER) . "\n\nGET:\n" . durDebugVar($_GET) . "\n\nREQUEST:\n" . durDebugVar($_REQUEST) . "\n";
      $sContent .= "\nCONSTANTS:\n" .
                   '  DUR_REQUEST_URI     => ' . durDebugEscape(DUR_REQUEST_URI) . "\n" .
                   '  DUR_HTTP_HOST       => ' . durDebugEscape(DUR_HTTP_HOST) . "\n" .
                   '  DUR_FULL_URI        => ' . durDebugEscape(DUR_FULL_URI) . "\n" .
                   '  DUR_ORIG_RURI       => ' . (defined('DUR_ORIG_RURI') ? durDebugEscape(DUR_ORIG_RURI) : 'NOT-SET') . "\n" .
                   '  DUR_SEO_REQUEST_URI => ' . (defined('DUR_SEO_REQUEST_URI') ? durDebugEscape(DUR_SEO_REQUEST_URI) : 'NOT-SET') . "\n";
                   
      $iTimeNow = microtime(true);
      $iTimeAll = ($iTimeNow - DUR_TIME_START) / 1000;
      $iTimeContent = ($iTimeStart - DUR_TIME_START) / 1000;
      $iTimeLinks = ($iTimeNow - $iTimeStart) / 1000;
      $sContent .= "\nTIME:\n" . 
                   '  ALL: ' . number_format($iTimeAll, 8) . " sec. (100%)\n" .
                   '  CMS: ' . number_format($iTimeContent, 8) . ' sec. (' . number_format($iTimeContent / $iTimeAll * 100, 2)  . "%)\n" . 
                   '  DUR: ' . number_format($iTimeLinks, 8) . ' sec. (' . number_format($iTimeLinks / $iTimeAll * 100, 2)  . "%)\n";
             
      $sContent .= "\nD-Data:\n" . durDebugVar($aDataToStore);
      $sContent .= '-->';
    }

    $sContent = durOutputCompress($sContent);
    if (defined('DUR_FIX_CONTLEN') && DUR_FIX_CONTLEN) {
      header('Content-Length: ' . strlen($sContent));
    }
    

 
    return $sContent;
  }

  function durOtherReplacer ($sContent) {
  

function outerlinks($matches){
$sEq = false; //есть ли совпадения
$res = $matches[0];
$arMassNotNoindex = array(
'demis.ru',
DUR_FIX_HTTP_HOST, 
); 
foreach($arMassNotNoindex as $item){
if (strpos($res,$item)) {$sEq = true; break;}
}
if (!$sEq){ // если совпадений не нашлось
// $res = '<noindex>'.$matches[0].'</noindex>';

//если отсутствует rel, то добавляем его 
if (!strpos($res, 'rel=')){
$res = str_replace('<a ','<a rel=nofollow ',$res);
}
}
return $res;
}
//закрываем исходящие ссылки в ноиндекс и нофоллоу
//$sContent = preg_replace_callback('%<a[^>]*href=[\'"]?http://.*</a>%siU','outerlinks',$sContent);


//$sContent = str_replace(array('<b>','</b>','<u>','</u>',),array('<span style="font-weight: bold;">','</span>','<span style="text-decoration: underline;">','</span>',),$sContent);


    return $sContent;
  }






/* Подключение в начале файла

// ЧПУ ---
  if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/d-url-rewriter.php')) {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/d-url-rewriter.php');
    durRun ();
  }
// --- ЧПУ

/* Для поддоменов неплохо было прописывать

RewriteCond %{HTTP_HOST} ^www.(.{4,}.nickon.ru)$
RewriteRule ^(.*)$ http://%1/$1 [R=301,L]

RewriteCond %{HTTP_HOST} ^(.{4,}).nickon.ru$
RewriteRule ^robots\.txt$ robots-%1.txt [L]

*/


/* Подключение с единой точкой входа
RemoveHandler .html .htm
AddType application/x-httpd-php .php .htm .html .phtml
php_value auto_prepend_file "d-url-rewriter.php"
*/

