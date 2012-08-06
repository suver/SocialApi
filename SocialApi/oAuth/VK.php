<?php
/**
 * Класс для работы с авторизацией в социальной сети Vkontakte
 * @author suver
 *
 */
class SocialApi_oAuth_VK extends SocialApi_oAuth_Abstract {
	
	protected $oAuthUrl = 'https://oauth.vk.com/';
	protected $accessToken = null;
	protected $appId = null;
	protected $appSecret = null;
	
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
		$this->setAppId ( $config['oAuth']['VK']['appId'] );
		$this->setAppSecret ( $config['oAuth']['VK']['appSecret'] );
		$this->setAccessToken ( $config['oAuth']['VK']['access_token'] );
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_oAuth_Abstract::createAuthUrl()
	 */
	public function createAuthUrl ( )
	{
		$config = SocialApi_Config::get ();
		$params = array (
				// идентификатор Вашего приложения
				'client_id' => $this->getAppId (),
				// запрашиваемые права доступа приложения
				'scope' 	=> $config['oAuth']['VK']['scope'], 
				// адрес, на который будет передан code. Этот адрес должен 
				// находиться в пределах домена, указанного в настройках приложения
				'redirect_uri' 	=> $config['oAuth']['VK']['redirect_uri'], 
				'response_type' => $config['oAuth']['VK']['response_type'],
		);
		
		return $this->createAuthUrlFromParams ( $params );
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_oAuth_Abstract::createAuthUrlFromParamt()
	 */
	public function createAuthUrlFromParams ( $params=array() )
	{
		$_params = array (
				// идентификатор Вашего приложения
				'client_id' => $this->getAppId (),
				// запрашиваемые права доступа приложения
				'scope' 	=> 'notify',
				// адрес, на который будет передан code. Этот адрес должен
				// находиться в пределах домена, указанного в настройках приложения
				'redirect_uri' 	=> 'http://api.vk.com/blank.html',
				'response_type' => 'token',
		);
	
		$params = array_merge( $_params, $params );
	
		$query = http_build_query($params, null, '&');
	
		return "{$this->oAuthUrl}authorize?{$query}";
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
				'code' => $code
			);
			
			$http = new SocialApi_Http();
			$responce = $http->get ( $this->oAuthUrl . 'access_token', $params );
			
		} catch (socialApiException $e) {
			// most likely that user very recently revoked authorization.
			// In any event, we don't have an access token, so say so.
			return false;
		}
		
		if ($responce) {
			$arr = json_decode ( $responce );
			if (empty ($arr->error))
			{
				$this->setAccessToken ( $arr->access_token );
				return $arr;
			}
			else 
			{
				throw new SocialApi_Exception_Auth( $arr->error . " - " . $arr->error_description );
				return false;
			}
		}
		return false;
	}
	
	
}