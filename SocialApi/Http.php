<?php
/**
 * Класс для работы с протоколом http
 * @author suver
 *
 */
class SocialApi_Http {
	
	protected $ch;
	protected $headers = array ();
	protected $userAgent = '';
	
	protected $_lastHttpCode = null; 
	protected $_lastHttpInfo = null;
	
	public function __construct ()
	{
		$this->ch = curl_init();
	}
	
	/**
	 * Подключает объект curl. Метод нужен для переопределения стандартного класса CURL на другой
	 * @param object $ch
	 */
	public function setCurlObject ( $ch ) 
	{
		if (is_object ($ch)) 
		{
			$this->ch =$ch;
		}
		$this->setUserAgent ( "socialApi v 0.1" );
	}
	
	/**
	 * Добавляет заголовок к запросу
	 * @param string $header
	 */
	public function setHeader ($header)
	{
		$this->headers[] = $header."\n";
	}
	
	/**
	 * Указывает UserAgent
	 * @param string $userAgent
	 */
	public function setUserAgent ($userAgent)
	{
		$this->userAgent = $userAgent;
	}
	
	/**
	 * Отправляет PUT запрос к серверу по указаному url
	 * @param string $url
	 * @param array $params
	 * @return mixed
	 */
	public function put ( $url, $params )
	{
		$this->checkUploadFiles ( $params );
		if (is_array ($params) AND (sizeof ($params) > 0)) {
			curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
		}
		return $this->exec ( $url );
	}
	
	/**
	 * Отправляет delete запрос к серверу по указаному url
	 * @param string $url
	 * @param array $params
	 * @return mixed
	 */
	public function delete ( $url, $params )
	{
		$this->checkUploadFiles ( $params );
		if (is_array ($params) AND (sizeof ($params) > 0)) {
			curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
		}
		return $this->exec ( $url );
	}
	
	/**
	 * Отправляет post запрос к серверу по указаному url
	 * @param string $url
	 * @param array $params
	 * @return mixed
	 */
	public function post ( $url, $params )
	{
		$this->checkUploadFiles ( $params );
		if (is_array ($params) AND (sizeof ($params) > 0)) {
			curl_setopt($this->ch, CURLOPT_POST, true);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
		}
		return $this->exec ( $url );
	} 
	
	/**
	 * Отправляет get запрос к url
	 * @param string $url
	 * @param array $params
	 * @return mixed
	 */
	public function get ( $url, $params )
	{
		if ($this->checkUploadFiles ( $params ))
		{
			return $this->post ( $url, $params );
		}
		
		if (is_array ($params) AND (sizeof ($params) > 0)) {
			//curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($params, null, '&'));
			$url = $url.'?'.http_build_query($params, null, '&');
		}

		return $this->exec ( $url );
	}
	
	/**
	 * Проверяет наличие в параметрах ключа требующих отправку файла. Если такой находится, 
	 * то проверяется рассположеине файла. В случае, если, по указаному пути файл не обнаружен
	 * уходим в Exception
	 * @param array $params
	 * @throws SocialApi_Exception
	 * @return boolean
	 */
	public function checkUploadFiles ($params)
	{
		if (is_array ($params))
		{
			foreach ($params as $k=>$param)
			{
				if (is_string ($param) AND preg_match ("#^@#is",$param))
				{
					$path = preg_replace ( "#^@#is", "", $param );
					if (!file_exists ($path)) 
					{
						throw new SocialApi_Exception ('Param ' . $k . ': file ' . $path . ' not founded');
					}
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Включает режим CURLOPT_VERBOSE
	 */
	public function enabledVerbose ()
	{
		curl_setopt($this->ch, CURLOPT_VERBOSE, true);
	}
	
	/**
	 * Отключает режим CURLOPT_VERBOSE
	 */
	public function disabledVerbose ()
	{
		curl_setopt($this->ch, CURLOPT_VERBOSE, false);
	}
	
	/**
	 * Отправляет запрос к URL
	 * @param strimg $url
	 * @throws socialApi_Exception
	 * @return mixed
	 */
	protected function exec ( $url ) 
	{
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, false);
		//curl_setopt($this->ch, CURLOPT_FAILONERROR, false);
		//curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
		//curl_setopt($this->ch, CURLOPT_HEADER, true);
		//curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 20);
		//curl_setopt($this->ch, CURLOPT_TIMEOUT, 60);
		//curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);
		curl_setopt($this->ch, CURLOPT_URL, $url);
		
		//$this->headers['If-Modified-Since'] = gmdate('D, d M Y H:i:s', mktime()-369454).' GMT';
		
		if (is_array($this->headers) AND sizeof ($this->headers) > 0) {
			curl_setopt($this->ch, CURLOPT_HEADER, true);
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
		}
		
		$result = curl_exec($this->ch);
		
		$_lastHttpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		
		$this->_lastHttpCode = $_lastHttpCode;
		
		$this->_lastHttpInfo = curl_getinfo ( $this->ch );
		
		if ($result === false) {
			$e = new socialApi_Exception (curl_errno($ch) . " - " . curl_error($ch));
			curl_close($this->ch);
			throw $e;
		}
		
		curl_close($this->ch);
		return $result;
	}
	
	/**
	 * Возвращает последний http code запроса
	 */
	public function getHTTPCODE ()
	{
		return $this->_lastHttpCode;
	}

	/**
	 * Возвращает массив информации о последнем отправленном http запросе
	 */
	public function curlHTTPInfo ()
	{
		return $this->_lastHttpInfo;
	}

	/**
	 * Возвращает массив информации о curl 
	 */
	public function curlInfo ()
	{
		return curl_version ();
	}
}