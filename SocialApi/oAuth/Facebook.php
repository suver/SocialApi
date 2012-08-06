<?php
/**
 * Класс для работы с авторизацией в социальной сети Vkontakte
 * @author suver
 *
 */
class SocialApi_oAuth_Facebook extends SocialApi_oAuth_Abstract {
	
	protected $oAuthUrl = 'https://www.facebook.com/dialog/oauth';
	protected $oAuthDeviceUrl = 'https://graph.facebook.com/oauth/device';
	protected $oAuthTokenUrl = 'https://graph.facebook.com/oauth/access_token';
	protected $redirectUri = null;
	protected $deviceCode = null;
	protected $accessToken = null;
	protected $appId = null;
	protected $appSecret = null;
	protected $state = null;
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_oAuth_Abstract::checkAuth()
	 */
	public function checkAuth ()
	{
		
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_oAuth_Abstract::setConfigure()
	 */
	public function setConfigure () 
	{
		$config = SocialApi_Config::get ();
		$this->state = md5(uniqid(mt_rand(), true));
		$this->setAppId ( $config['oAuth']['Facebook']['appId'] );
		$this->setAppSecret ( $config['oAuth']['Facebook']['appSecret'] );
		$this->setAccessToken ( $config['oAuth']['Facebook']['access_token'] );
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_oAuth_Abstract::createAuthUrl()
	 */
	public function createAuthUrl ( )
	{
		$config = SocialApi_Config::get ();
		if (isset ($config['oAuth']['Facebook']['type']))
		{
			$params = array (
					// идентификатор Вашего приложения
					'client_id' => $this->getAppId (),
					// запрашиваемые права доступа приложения
					'scope' 	=> $config['oAuth']['Facebook']['scope'],
					// Supported types: web_server, user_agent, client_cred, username
					'type'		=> 'web_server',
			);
			$this->deviceCode = $params['type'];
		}
		else
		{
			$params = array (
					// идентификатор Вашего приложения
					'client_id' => $this->getAppId (),
					// запрашиваемые права доступа приложения
					'scope' 	=> $config['oAuth']['Facebook']['scope'],
					// адрес, на который будет передан code. Этот адрес должен
					// находиться в пределах домена, указанного в настройках приложения
					'redirect_uri' 	=> $config['oAuth']['Facebook']['redirect_uri'],
					'state' => $this->state,
			);
			$this->redirectUri = $params['redirect_uri'];
		}
		
		return $this->createAuthUrlFromParams ( $params );
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_oAuth_Abstract::createAuthUrlFromParamt()
	 */
	public function createAuthUrlFromParams ( $params=array() )
	{
		$config = SocialApi_Config::get ();
		if (isset ($params['type']))
		{
			$_params = array (
					// идентификатор Вашего приложения
					'client_id' => $this->getAppId (),
					// запрашиваемые права доступа приложения
					'scope' 	=> $config['oAuth']['Facebook']['scope'], 
					// Supported types: web_server, user_agent, client_cred, username
					'type'		=> 'web_server',
			);
			$this->deviceCode = $params['type'];
		}
		else 
		{
			$_params = array (
					// идентификатор Вашего приложения
					'client_id' => $this->getAppId (),
					// запрашиваемые права доступа приложения
					'scope' 	=> $config['oAuth']['Facebook']['scope'],
					// адрес, на который будет передан code. Этот адрес должен
					// находиться в пределах домена, указанного в настройках приложения
					'redirect_uri' 	=> $config['oAuth']['Facebook']['redirect_uri'],
					'state' => $this->state,
			);
			$this->redirectUri = $params['redirect_uri'];
		}
	
		$params = array_merge( $_params, $params );
		
		$query = http_build_query($params, null, '&');
	
		if (isset ($params['type']))
		{
				return "{$this->oAuthDeviceUrl}?{$query}";
		}
		else 
		{
			return "{$this->oAuthUrl}?{$query}";
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_oAuth_Abstract::verifyIdToken()
	 */
	public function verifyIdToken ($accessToken = null)
	{
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_oAuth_Abstract::refreshAccessToken()
	 */
	public function refreshAccessToken ()
	{
		
	}
	
	/**
	 * Получает параметр из переменной _GET['code']
	 */
	public function getHttpCode () {
		return isset ($_GET['code']) ? $_GET['code'] : false ;
	}
	
	
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_oAuth_Abstract::requestAccessToken()
	 */
	public function requestAccessToken ()
	{
		if (!empty ($this->deviceCode))
		{
			return $this->_requestAccessTokenDevice ();
		}
		else 
		{
			return $this->_requestAccessTokenSite ();
		}
	}
	
	/**
	 * Получает access_token для сайта
	 */
	protected function _requestAccessTokenSite ()
	{
		$code = $this->getHttpCode ();
		if (!$code)
		{
			return false;
		}
	
		try {
			// need to circumvent json_decode by calling _oauthRequest
			// directly, since response isn't JSON format.
			$params = array(
					'client_id' => $this->getAppId (),
					'client_secret' => $this->getAppSecret (),
					'redirect_uri' => $this->redirectUri,
					'code' => $code,
					'state' => $this->state,
			);
				
			$http = new SocialApi_Http();
			$responce = $http->get ( $this->oAuthTokenUrl, $params );
				
		} catch (socialApiException $e) {
			// most likely that user very recently revoked authorization.
			// In any event, we don't have an access token, so say so.
			return false;
		}
	
		if ($responce) {
				
			if (!preg_match ("#^{#is", $responce))
			{
				parse_str($responce, $arr);
				$this->setAccessToken ( $arr['access_token'] );
				return $arr;
			}
			else {
				$arr = json_decode($responce);
				throw new SocialApi_Exception_Auth( $arr->error->code . " - " . $arr->error->message );
				return false;
			}
		}
		return false;
	}
	
	/**
	 * Получает access_token для девайса
	 */
	protected function _requestAccessTokenDevice ()
	{
		$code = $this->getHttpCode ();
		if (!$code)
		{
			return false;
		}
	
		try {
			// need to circumvent json_decode by calling _oauthRequest
			// directly, since response isn't JSON format.
			$params = array(
					'client_id' => $this->getAppId (),
					'type' => $this->deviceCode,
					'code' => $code,
			);
	
			$http = new SocialApi_Http();
			$responce = $http->get ( $this->oAuthDeviceUrl, $params );
	
		} catch (socialApiException $e) {
			// most likely that user very recently revoked authorization.
			// In any event, we don't have an access token, so say so.
			return false;
		}
	
		if ($responce) {
	
			$arr = json_decode($responce);
			if (empty ($arr->error))
			{
				$this->setAccessToken ( $arr->access_token );
				return $arr;
			}
			else {
				$arr = json_decode($responce);
				throw new SocialApi_Exception_Auth( $arr->error->message );
				return false;
			}
		}
		return false;
	}
	

}