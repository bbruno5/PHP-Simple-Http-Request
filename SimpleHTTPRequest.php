<?PHP
/**
 * PHP Version 5.4+
 * PHP Version 7.0+
 *
 * @copyright 2019 B5 Team
 * @author bbruno5
 * @link https://github.com/bbruno5/PHP-Simple-Http-Request
 */

/*

** Get Http object setting some parameters
$http = new SimpleHTTP\Request(null, [], false, false, false, true, false);

** Get Http object without configure parameters
$http = new SimpleHTTP\Request;

** Making a POST request
$http->post(array(
	"url" => $url,
	"params" => array(
		"arg1" => "something",
		"arg2" => "something",
		"arg3" => "something",
		"arg4" => "something",
		"arg5" => "something"
	),
	"headers" => array(
		"header1" => "something",
		"header2" => "something",
		"header3" => "something",
		"header4" => "something",
		"header5" => "something"
	),
	"user" => "something",
	"password" => "something",
	"verifySSLPeer" => true,
	"verifySSLHost" => true,
	"sendAsJsonData" => true,
	"fixBodyContent" => true
));

** Making a PUT request
$http->put(array(
	"url" => $url,
	"params" => array(
		"arg1" => "something",
		"arg2" => "something",
		"arg3" => "something",
		"arg4" => "something",
		"arg5" => "something"
	),
	"headers" => array(
		"header1" => "something",
		"header2" => "something",
		"header3" => "something",
		"header4" => "something",
		"header5" => "something"
	),
	"user" => "something",
	"password" => "something",
	"verifySSLPeer" => true,
	"verifySSLHost" => true,
	"sendAsJsonData" => true,
	"fixBodyContent" => true
));

** Making a GET request
$http->post(array(
	"url" => $url,
	"params" => array(
		"arg1" => "something",
		"arg2" => "something",
		"arg3" => "something",
		"arg4" => "something",
		"arg5" => "something"
	),
	"headers" => array(
		"header1" => "something",
		"header2" => "something",
		"header3" => "something",
		"header4" => "something",
		"header5" => "something"
	),
	"verifySSLPeer" => true,
	"verifySSLHost" => true,
	"fixBodyContent" => false
));

** Make POST request with less arguments (seetting only url and params)
$http->post(array(
	"url" => $url,
	"params" => array(
		"arg1" => "something",
		"arg2" => "something",
		"arg3" => "something",
		"arg4" => "something",
		"arg5" => "something"
	),
	"fixBodyContent" => true
));

** Get Http object setting all parameters and call GET request
$http = new SimpleHTTP\Request("https://example.com/call", $params, true, true);

$http->get();

*/

namespace SimpleHTTP;

error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Content-type: text/html; charset=UTF-8');

class Request {
	
	private $url;
	private $params;
	private $headers;
	private $user;
	private $password;
	private $verifySSLPeer;
	private $verifySSLHost;
	private $sendAsJsonData;
	private $fixBodyContent;
	
	/**
	* Creates a new instance of the Http class.
	* Params must be associative arrays.
	* verifySSLPeer, verifySSLHost and sendAsJsonData parameters are default true.
	* fixBodyContent parameter are default false.
	*
	* @param array arguments
	* @param string $url
	* @param array $params
	* @param array $headers
	* @param boolean $verifySSLPeer
	* @param boolean $verifySSLHost
	* @param boolean $sendAsJsonData
	* @param boolean $fixBodyContent
	*/
	public function __construct($url = "", $params = [], $headers = [], $user = null, $password = null, $verifySSLPeer = true, $verifySSLHost = 2, $sendAsJsonData = false, $fixBodyContent = false) {
		
		$this->url = $url;
		$this->params = $params;
		$this->headers = $headers;
		$this->user = $user;
		$this->password = $password;
		$this->verifySSLPeer = $verifySSLPeer;
		$this->verifySSLHost = $verifySSLHost;
		$this->sendAsJsonData = $sendAsJsonData;
		$this->fixBodyContent = $fixBodyContent;
	}
	
	/**
	* Makes CURL GET request.
	*
	* @return body
	*/
	public function get($args = []) {
		
		$headers = [];
		
		if (count($args) > 0) {
			
			$this->structure($args);
		}
		
		$this->url .= "?";
		
		foreach ($this->params as $name => $value) {
			
			$this->url .= $name . "=" . $value . "&";
		}
		
		if (substr($this->url, -1) == "&") {
			
			$this->url = substr_replace($this->url, "", -1);
		}
		
		if (count($this->headers) > 0) {
			
			foreach ($this->headers as $name => $value) {
				
				array_push($headers, (string)$name . ': ' . (string)$value);
			}
		} else {
			
			$headers = 1;
		}
		
		$i = curl_init();
		curl_setopt($i, CURLOPT_URL, $this->url);
		curl_setopt($i, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		
		if ($this->user && $this->password) {
			
			curl_setopt($i, CURLOPT_USERPWD, $this->user . ":" . $this->password);
		}
		
		curl_setopt($i, CURLOPT_SSL_VERIFYPEER, $this->verifySSLPeer);
		curl_setopt($i, CURLOPT_SSL_VERIFYHOST, $this->verifySSLHost);
		curl_setopt($i, CURLOPT_HEADER, $headers);
		curl_setopt($i, CURLOPT_RETURNTRANSFER, 1);
		
		$response = curl_exec($i);
		curl_close($i);
		
		$x = explode("\r\n\r\n", $response, 3);
		$header = $x[0];
		
		if ($header['Response Code'] == 100) {
			
			$header = $x[1];
			$body = $x[2];
		}else{
			
			$body = $x[1];
		}
		
		if ($this->fixBodyContent) {
			
			$body = substr_replace($body, "", 0, 1);
			$body = substr_replace($body, "", -1);
		}
		
		$arr = array(
			"headers" => $header,
			"body" => json_decode($body, true)
		);
		
		return json_decode(json_encode($arr), true);
	}
	
	/**
	* Makes CURL POST request.
	*
	* @return body
	*/
	public function post($args = []) {
		
		$headers = [];
		
		if (count($args) > 0) {
			
			$this->structure($args);
		}
		
		if (count($this->headers) > 0) {
			
			foreach ($this->headers as $name => $value) {
				
				array_push($headers, (string)$name . ': ' . (string)$value);
			}
		} else {
			
			$headers = 1;
		}
		
		$i = curl_init();
		curl_setopt($i, CURLOPT_URL, $this->url);
		curl_setopt($i, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		
		if ($this->sendAsJsonData) {
			
			$params = json_encode($this->params);
			curl_setopt($i, CURLOPT_POSTFIELDS, json_encode($this->params));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
				'Content-Type: application/json',                                                                                
				'Content-Length: ' . strlen($params))                                                                       
			);
		} else {
			
			curl_setopt($i, CURLOPT_POSTFIELDS, http_build_query($this->params));
		}
		
		if ($this->user && $this->password) {
			
			curl_setopt($i, CURLOPT_USERPWD, $this->user . ":" . $this->password);
		}
		
		curl_setopt($i, CURLOPT_SSL_VERIFYPEER, $this->verifySSLPeer);
		curl_setopt($i, CURLOPT_SSL_VERIFYHOST, $this->verifySSLHost);
		curl_setopt($i, CURLOPT_HEADER, $headers);
		curl_setopt($i, CURLOPT_RETURNTRANSFER, 1);
		
		$response = curl_exec($i);
		curl_close($i);
		
		$x = explode("\r\n\r\n", $response, 3);
		$header = $x[0];
		
		if ($header['Response Code'] == 100) {
			
			$header = $x[1];
			$body = $x[2];
		}else{
			
			$body = $x[1];
		}
		
		if ($this->fixBodyContent) {
			
			$body = substr_replace($body, "", 0, 1);
			$body = substr_replace($body, "", -1);
		}
		
		$arr = array(
			"headers" => $header,
			"body" => json_decode($body, true)
		);
		
		return json_decode(json_encode($arr), true);
	}
	
	/**
	* Makes CURL PUT request.
	*
	* @return body
	*/
	public function put($args = []) {
		
		$headers = [];
		
		if (count($args) > 0) {
			
			$this->structure($args);
		}
		
		if (count($this->headers) > 0) {
			
			foreach ($this->headers as $name => $value) {
				
				array_push($headers, (string)$name . ': ' . (string)$value);
			}
		} else {
			
			$headers = 1;
		}
		
		$i = curl_init();
		curl_setopt($i, CURLOPT_URL, $this->url);
		curl_setopt($i, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		
		if ($this->sendAsJsonData) {
			
			$params = json_encode($this->params);
			curl_setopt($i, CURLOPT_POSTFIELDS, json_encode($this->params));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
				'Content-Type: application/json',                                                                                
				'Content-Length: ' . strlen($params))                                                                       
			);
		} else {
			
			curl_setopt($i, CURLOPT_POSTFIELDS, http_build_query($this->params));
		}
		
		if ($this->user && $this->password) {
			
			curl_setopt($i, CURLOPT_USERPWD, $this->user . ":" . $this->password);
		}
		
		curl_setopt($i, CURLOPT_SSL_VERIFYPEER, $this->verifySSLPeer);
		curl_setopt($i, CURLOPT_SSL_VERIFYHOST, $this->verifySSLHost);
		curl_setopt($i, CURLOPT_HEADER, $headers);
		curl_setopt($i, CURLOPT_RETURNTRANSFER, 1);
		
		$response = curl_exec($i);
		curl_close($i);
		
		$x = explode("\r\n\r\n", $response, 3);
		$header = $x[0];
		
		if ($header['Response Code'] == 100) {
			
			$header = $x[1];
			$body = $x[2];
		}else{
			
			$body = $x[1];
		}
		
		if ($this->fixBodyContent) {
			
			$body = substr_replace($body, "", 0, 1);
			$body = substr_replace($body, "", -1);
		}
		
		$arr = array(
			"headers" => $header,
			"body" => json_decode($body, true)
		);
		
		return json_decode(json_encode($arr), true);
	}
	
	private function structure($args) {
		
		if ($args['url'] != null) {
			
			$this->url = $args['url'];
		}
		
		if ($args['params'] != null) {
			
			$this->params = $args['params'];
		}
		
		if ($args['headers'] != null) {
			
			$this->headers = $args['headers'];
		}
		
		if ($args['user'] != null) {
			
			$this->user = $args['user'];
		}
		
		if ($args['password'] != null) {
			
			$this->password = $args['password'];
		}
		
		if ($args['verifySSLPeer'] != null) {
			
			$this->verifySSLPeer = $args['verifySSLPeer'];
		}
		
		if ($args['verifySSLHost'] != null) {
			
			if ($args['verifySSLHost'] == true) {
				
				$args['verifySSLHost'] = 2;
			}
			
			$this->verifySSLHost = $args['verifySSLHost'];
		}
		
		if ($args['sendAsJsonData'] != null) {
			
			$this->fixBodyContent = $args['fixBodyContent'];
		}
		
		if ($args['fixBodyContent'] != null) {
			
			$this->fixBodyContent = $args['fixBodyContent'];
		}
	}
	
	private function console_log($data){
		
		echo '<script>';
		echo 'console.log('. json_encode($data) .')';
		echo '</script>';
	}
}

?>
