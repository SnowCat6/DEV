<? function module_soap($fn, &$data)
{
	@list($fn, $val) = explode(':', $fn, 2);
	$fn = getFn("soap_$fn");
	return $fn?$fn($val, $data):NULL;
}?>
<? function soap_login($fn, &$data){
	$GLOBALS['_CONFIG']['soap'] = $data;
}?>
<? function soap_exec($fn, &$data)
{
	$timeout = max(0, (int)sessionTimeout() - 2);
	$timeout = max(30, $timeout);
	if (!$timeout) return;
	
	global $_CONFIG;
	@$config = $_CONFIG['soap'];
	if (!$config) return;

    $params = array(
		  'login'	=>	@$config['login']
        , 'password'=>	@$config['password']
        , 'features'=>	SOAP_SINGLE_ELEMENT_ARRAYS
		, 'trace'	=> 1
		, 'timeout'	=> $timeout * 1000
		, 'sslverifypeer' => false
    );

	try {
		$client = new SoapClientTimeout($config['wsdl'], $params);
		$xml = $client->__call($fn, $data);
		return $xml;
	}catch (SoapFault $E) {
		m('message:error:SOAP', $E->faultstring);
	}
}?>
<? function soap_curl($url, &$data)
{
	$timeout = max(0, (int)sessionTimeout() - 2);
	if (!$timeout) return;
	
	// Call via Curl and use the timeout
	$curl = curl_init($url);
	if ($curl === false)
		throw new Exception('Curl initialisation failed');

	if (defined('CURLOPT_TIMEOUT_MS')) {	//Timeout in MS supported? 
		curl_setopt($curl, CURLOPT_TIMEOUT_MS, $timeout * 1000);
	}else{ //Round(up) to second precision
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
	}
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl, CURLOPT_POST, false);
	
	$response = curl_exec($curl);
	curl_close($curl);

	return $response;
}?>
<?
//	Drop-in replacement for PHP's SoapClient class supporting connect and response/transfer timeout
//	Usage: Exactly as PHP's SoapClient class, except that 3 new options are available:
//	timeout			The response/transfer timeout in milliseconds; 0 == default SoapClient / CURL timeout
//	connecttimeout	The connection timeout; 0 == default SoapClient / CURL timeout
//	sslverifypeer	FALSE to stop SoapClient from verifying the peer's certificate
class SoapClientTimeout extends SoapClient
{
	private $timeout = 0;
	private $connecttimeout = 0;
	private $sslverifypeer = true;
	
	public function __construct($wsdl, $options) {
		//"POP" our own defined options from the $options array before we call our parent constructor
		//to ensure we don't pass unknown/invalid options to our parent
		if (isset($options['timeout'])) {
			$this->__setTimeout($options['timeout']);
			unset($options['timeout']);
		}
		if (isset($options['connecttimeout'])) {
			$this->__setConnectTimeout($options['connecttimeout']);
			unset($options['connecttimeout']);
		}
		if (isset($options['sslverifypeer'])) {
			$this->__setSSLVerifyPeer($options['sslverifypeer']);
			unset($options['sslverifypeer']);
		}
		//Now call parent constructor
		parent::__construct($wsdl, $options);
	}
	
	public function __setTimeout($timeoutms)
	{
		if (!is_int($timeoutms) && !is_null($timeoutms) || $timeoutms<0)
			throw new Exception("Invalid timeout value");
		
		$this->timeout = $timeoutms;
	}
	
	public function __getTimeout()
	{
		return $this->timeout;
	}
	
	public function __setConnectTimeout($connecttimeoutms)
	{
		if (!is_int($connecttimeoutms) && !is_null($connecttimeoutms) || $connecttimeoutms<0)
			throw new Exception("Invalid connecttimeout value");
		
		$this->connecttimeout = $connecttimeoutms;
	}
	
	public function __getConnectTimeout()
	{
		return $this->connecttimeout;
	}
	
	public function __setSSLVerifyPeer($sslverifypeer)
	{
		if (!is_bool($sslverifypeer))
			throw new Exception("Invalid sslverifypeer value");
		
		$this->sslverifypeer = $sslverifypeer;
	}
	
	public function __getSSLVerifyPeer()
	{
		return $this->sslverifypeer;
	}
	
	public function __doRequest($request, $location, $action, $version, $one_way = FALSE)
	{
		if (($this->timeout===0) && ($this->connecttimeout===0))
		{
			// Call via parent because we require no timeout
			$response = parent::__doRequest($request, $location, $action, $version, $one_way);
		}
		else
		{
			// Call via Curl and use the timeout
			$curl = curl_init($location);
			if ($curl === false)
				throw new Exception('Curl initialisation failed');

			$$version == 2 ? 'application/soap+xml' : 'text/xml';

			$options = array(
				CURLOPT_VERBOSE => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $request,
				CURLOPT_HEADER => false,
				CURLOPT_NOSIGNAL => true,	//http://www.php.net/manual/en/function.curl-setopt.php#104597
				CURLOPT_HTTPHEADER => array("Content-Type: $version", "SOAPAction: $action"),
				CURLOPT_SSL_VERIFYPEER => $this->sslverifypeer
				);
			

			if ($this->timeout > 0) {
				if (defined('CURLOPT_TIMEOUT_MS')) {	//Timeout in MS supported? 
					$options[CURLOPT_TIMEOUT_MS]= $this->timeout;		
				} else	{ //Round(up) to second precision
					$options[CURLOPT_TIMEOUT]	= ceil($this->timeout/1000);	
				}
			}
 
			if ($this->connecttimeout > 0) {
				if (defined('CURLOPT_CONNECTTIMEOUT_MS')) {	//ConnectTimeout in MS supported? 
					$options[CURLOPT_CONNECTTIMEOUT_MS]	= $this->connecttimeout;	
				} else { //Round(up) to second precision
					$options[CURLOPT_CONNECTTIMEOUT]	= ceil($this->connecttimeout/1000);	
				}
			}
			
			if (curl_setopt_array($curl, $options) === false)
				throw new Exception('Failed setting CURL options');

			if (isset($this->_login)){
				// setting the authorization method to BASIC: 
				curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
				// supplying your credentials: 
				curl_setopt($curl, CURLOPT_USERPWD, $this->_login . ":" . $this->_password);
			}
			$response = curl_exec($curl);
			if (curl_errno($curl))
				throw new SoapFault("CURL", curl_error($curl));
			
			curl_close($curl);
		}
//		echo htmlspecialchars($response);
		// Return?
		if (!$one_way) return ($response);
	}
}
?>
