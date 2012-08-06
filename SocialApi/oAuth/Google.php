<?php
/**
 * Класс для работы с авторизацией в социальной сети Vkontakte
 * @author suver
 *
 */
class SocialApi_oAuth_Google extends SocialApi_oAuth_Abstract {
	
	protected $oAuthUrl = 'https://accounts.google.com/o/oauth2/auth';
	protected $oAuthTokenUrl = 'https://accounts.google.com/o/oauth2/token';
	protected $oAuthTokenInfUrl = 'https://www.googleapis.com/oauth2/v1/tokeninfo';
	protected $redirectUri = null;
	protected $accessToken = null;
	protected $appId = null;
	protected $appSecret = null;
	protected $state = null;
	protected $code = null;
	protected $refresh_token = null;
	
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
		if (isset ($_SESSION['SA_STATE']))
		{
			$this->state = $_SESSION['SA_STATE'];
		}
		else 
		{
			$this->state = md5(uniqid(mt_rand(), true));
		}
		if (isset ($config['oAuth']['Google']['client_id']))
			$this->setAppId ( $config['oAuth']['Google']['client_id'] );

		if (isset ($config['oAuth']['Google']['client_secret']))
			$this->setAppSecret ( $config['oAuth']['Google']['client_secret'] );

		if (isset ($config['oAuth']['Google']['access_token']))
			$this->setAccessToken ( $config['oAuth']['Google']['access_token'] );
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_oAuth_Abstract::createAuthUrl()
	 */
	public function createAuthUrl ( )
	{
		$config = SocialApi_Config::get ();
		if (isset ($config['oAuth']['Google']['response_type']) AND ($config['oAuth']['Google']['response_type'] == 'token'))
		{
			$params = array (
					// идентификатор Вашего приложения
					'client_id' => $this->getAppId (),
					'access_type' => $config['oAuth']['Google']['access_type'],
					'redirect_uri' => $config['oAuth']['Google']['redirect_uri'],
					'response_type' => $config['oAuth']['Google']['response_type'],
					'approval_prompt' => $config['oAuth']['Google']['approval_prompt'],
					// запрашиваемые права доступа приложения
					'scope' 	=> $config['oAuth']['Google']['scope'], 
					'state' 	=> $this->state,
			);
		}
		else 
		{
			$params = array (
					// идентификатор Вашего приложения
					'client_id' => $this->getAppId (),
					'response_type' => $config['oAuth']['Google']['response_type'],
					'access_type' => $config['oAuth']['Google']['access_type'],
					'redirect_uri' => $config['oAuth']['Google']['redirect_uri'],
					'approval_prompt' => $config['oAuth']['Google']['approval_prompt'],
					// запрашиваемые права доступа приложения
					'scope' 	=> $config['oAuth']['Google']['scope'], 
					'state' 	=> $this->state,
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
		if (isset ($params['response_type']) AND ($params['response_type'] == 'token'))
		{
			$_params = array (
					// идентификатор Вашего приложения
					'client_id' => $this->getAppId (),
					'response_type' => $config['oAuth']['Google']['response_type'],
					'approval_prompt' => $config['oAuth']['Google']['approval_prompt'],
					'redirect_uri' => $config['oAuth']['Google']['redirect_uri'],
					// запрашиваемые права доступа приложения
					'scope' 	=> $config['oAuth']['Google']['scope'], 
					'state' 	=> $this->state,
			);
		}
		else 
		{
			$_params = array (
					// идентификатор Вашего приложения
					'client_id' => $this->getAppId (),
					'response_type' => $config['oAuth']['Google']['response_type'],
					'access_type' => $config['oAuth']['Google']['access_type'],
					'approval_prompt' => $config['oAuth']['Google']['approval_prompt'],
					'redirect_uri' => $config['oAuth']['Google']['redirect_uri'],
					// запрашиваемые права доступа приложения
					'scope' 	=> $config['oAuth']['Google']['scope'], 
					'state' 	=> $this->state,
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
		try {
			// need to circumvent json_decode by calling _oauthRequest
			// directly, since response isn't JSON format.
			$params = array(
					'access_token' => empty ($accessToken) ? $this->getAccessToken() : $accessToken ,
			);
		
			$http = new SocialApi_Http();
			$responce = $http->get ( $this->oAuthTokenInfUrl, $params );
		
		} catch (socialApiException $e) {
			// most likely that user very recently revoked authorization.
			// In any event, we don't have an access token, so say so.
			return false;
		}
		
		if ($responce) {
		
			$arr = json_decode($responce);
			if (empty ($arr->error))
			{
				return $arr;
			}
			else {
				//throw new SocialApi_Exception_Auth( $arr->error . ' - ' . $arr->error_description );
				return false;
			}
		}
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_oAuth_Abstract::refreshAccessToken()
	 */
	public function refreshAccessToken ()
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
					'refresh_token' => $this->refresh_token,
					'grant_type' => 'authorization_code',
			);
		
			$http = new SocialApi_Http();
			$responce = $http->get ( $this->oAuthTokenUrl, $params );
			//var_dump ($params,$http->curlHTTPInfo ());
			//var_dump ($responce);
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
		
				throw new SocialApi_Exception_Auth( $arr->error );
				return false;
			}
		}
		return false;
	}
	
	/**
	 * Получает параметр из переменной _GET['code']
	 */
	public function getHttpCode () {
		if (empty ($this->code))
		{
			return isset ($_GET['code']) ? $_GET['code'] : false ;
		}
		else 
		{
			return $this->code ;
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_oAuth_Abstract::requestAccessToken()
	 */
	public function requestAccessToken ()
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
					'grant_type' => 'authorization_code',
			);
				
			$http = new SocialApi_Http();
			$responce = $http->get ( $this->oAuthTokenUrl, $params );
			//var_dump ($params,$http->curlHTTPInfo ());
			//var_dump ($responce);
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
				if (!empty ($arr->refresh_token))
				{
					$this->setRefreshToken ( $arr->refresh_token );
				}
				return $arr;
			}
			else {
	
				throw new SocialApi_Exception_Auth( $arr->error );
				return false;
			}
		}
		return false;
	}
	
	/**
	 * Устанавливает refresh_token
	 */
	public function setRefreshToken ($refreshToken)
	{
		$this->refresh_token = $refreshToken;
	}
	
	/**
	 * Возвращает refresh_token
	 */
	public function getRefreshToken ()
	{
		return $this->refresh_token;
	}
}