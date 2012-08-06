<?php
/**
 * Класс для доступа к АПИ социальной сети Facebook
 * @author suver
 *
 */
class SocialApi_Api_Comunity_Google extends SocialApi_Api_Comunity_Abstract {
	
	protected $apiUrl = 'https://www.googleapis.com/plus/v1/';
	
	protected $meInfo = null;
	protected $meId = null;
	protected $meName = null;
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_Api_Comunity_Abstract::me()
	 */
	public function me ()
	{
		$userInfo = $this->api ('people/me');
		$this->meInfo = $userInfo;
		$this->meId = $userInfo->id;
		$this->meName = $userInfo->name;
		
		if (isset ($userInfo->birthday))
		{
			$bdate = explode ("/",$userInfo->birthday);
			$userInfo->birthday = $bdate['1'] . "." . $bdate['0'] . "." . $bdate['2'];
		}
		
		$info = array (
				"id" 		=> $this->meInfo->id,
				"name" 		=> isset ($this->meInfo->displayName) ? $this->meInfo->displayName : "",
				"first_name" => isset ($this->meInfo->name->givenName) ? $this->meInfo->name->givenName : "",
				"last_name" => isset ($this->meInfo->name->familyName) ? $this->meInfo->name->familyName : "",
				"username" 	=> isset ($this->meInfo->nickname) ? $this->meInfo->nickname : "",
				"screen_name" => isset ($this->meInfo->nickname) ? $this->meInfo->nickname : "",
				"birthday" 	=> isset ($this->meInfo->birthday) ? $this->meInfo->birthday : "",
				"link" 		=> isset ($this->meInfo->url) ? $this->meInfo->url : "",
				"quotes" 	=> isset ($this->meInfo->aboutMe) ? $this->meInfo->aboutMe : "",
				//"email" 	=> isset ($this->meInfo->email) ? $this->meInfo->email : "",
				"gender" 	=> isset ($this->meInfo->gender) ? $this->meInfo->gender : "",
				"timezone" 	=> 0,
		);
		
		return (object) $info;
	}
	

	
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_Api_Comunity_Abstract::get()
	 */
	public function get ($objectId)
	{
		$userInfo = $this->api ('people/'.$objectId);
		
		if (isset ($userInfo->birthday))
		{
			$bdate = explode ("/",$userInfo->birthday);
			$userInfo->birthday = $bdate['1'] . "." . $bdate['0'] . "." . $bdate['2'];
		}
		
		$info = array (
				"id" 		=> $userInfo->id,
				"name" 		=> isset ($userInfo->displayName) ? $userInfo->displayName : "",
				"first_name" => isset ($userInfo->name->givenName) ? $userInfo->name->givenName : "",
				"last_name" => isset ($userInfo->name->familyName) ? $userInfo->name->familyName : "",
				"username" 	=> isset ($userInfo->nickname) ? $userInfo->nickname : "",
				"screen_name" => isset ($userInfo->nickname) ? $userInfo->nickname : "",
				"birthday" 	=> isset ($userInfo->birthday) ? $userInfo->birthday : "",
				"link" 		=> isset ($userInfo->url) ? $userInfo->url : "",
				"quotes" 	=> isset ($userInfo->aboutMe) ? $userInfo->aboutMe : "",
				//"email" 	=> isset ($userInfo->email) ? $userInfo->email : "",
				"gender" 	=> isset ($userInfo->gender) ? $userInfo->gender : "",
				"timezone" 	=> 0,
		);
		
		return (object) $info;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_Api_Comunity_Abstract::feed()
	 */
	public function feed ($objectId='me')
	{
		$objectId = ($objectId == 'me') ? 'me' : $objectId ;
		
		$posts = $this->api ("people/{$objectId}/activities/public",array ('alt'=>'json','maxResults'=>100));
		
		
		$records = array ();
		foreach ($posts->items	 as $record)
		{
			//
			if (is_object ($record))
			{
				$repost = ($record->verb == 'share') ? true : false;

				$attachments = array ();
				if (isset ($record))
				{
					if (isset ($record->object->attachments))
					{
						foreach ($record->object->attachments as $attachment) 
						{
							$attachment = $this->modifyAttachment (
									$attachment,
									($repost) ? $record->object->actor->id : $record->actor->id, 
									$record->updated
							);
							$attachments[] = (object) $attachment;
						}
					}
				}
			}
			
			// Заносим коментарии пользователей
			$comments = array ();
			if (isset($record->object->replies->totalItems) AND ($record->object->replies->totalItems > 0)) 
			{
				$_comments = $this->api ("activities/{$record->id}/comments",array ('alt'=>'json','maxResults'=>100));
				
				foreach ($_comments->items as $comment)
				{
					if (is_object ($comment))
					$comments[] = array (
						'id' 	=> $comment->id,
						'from_id'	=> $comment->actor->id,
						//'from_name'	=> $comment->from->name,
						'message'	=> $comment->object->content,
						'date'	=> date_create($comment->updated),
					);
				}
			}
			
			
			// Заносим количество лайков
			$likes = 0;
			if (isset ($record->object->plusoners->totalItems))
			{
				$likes = $record->object->plusoners->totalItems;
			}
			
			
			
			$records[] = array (
				'id' 		=> $record->id,
				'from_id' 	=> $record->actor->id,
				'date' 	=> date_create($record->updated),
				'text' 	=> ($repost) ? $record->annotation : $record->object->content,
				'attachments' => $attachments,
				'owner' 	=> isset ($record->object->actor->id) ? $record->object->actor->id : $record->actor->id,
				'repost' 	=> $repost,
				'repost_text' 	=> ($repost) ? $record->object->content : null,
				'record_type' => !empty ($record->verb) ? $record->verb : 'text',
				'comments'	=> $comments,
				'likes'		=> $likes,
			);
			
			
		}
		
		return $records;
	}

	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_Api_Comunity_Abstract::search()
	 */
	public function search ($search)
	{
		$posts = $this->api ("activities",array ('alt'=>'json','maxResults'=>20,'query'=>$search));
		
		$records = array ();
		foreach ($posts->items	 as $record)
		{
			//
			if (is_object ($record))
			{
				$repost = ($record->verb == 'share') ? true : false;

				$attachments = array ();
				if (isset ($record))
				{
					if (isset ($record->object->attachments))
					{
						foreach ($record->object->attachments as $attachment) 
						{
							$attachment = $this->modifyAttachment (
									$attachment,
									($repost) ? $record->object->actor->id : $record->actor->id, 
									$record->updated
							);
							$attachments[] = (object) $attachment;
						}
					}
				}
			}
			
			// Заносим коментарии пользователей
			$comments = array ();
			if (isset($record->object->replies->totalItems) AND ($record->object->replies->totalItems > 0)) 
			{
				$_comments = $this->api ("activities/{$record->id}/comments",array ('alt'=>'json','maxResults'=>100));
				
				foreach ($_comments->items as $comment)
				{
					if (is_object ($comment))
					$comments[] = array (
						'id' 	=> $comment->id,
						'from_id'	=> $comment->actor->id,
						//'from_name'	=> $comment->from->name,
						'message'	=> $comment->object->content,
						'date'	=> date_create($comment->updated),
					);
				}
			}
			
			
			// Заносим количество лайков
			$likes = 0;
			if (isset ($record->object->plusoners->totalItems))
			{
				$likes = $record->object->plusoners->totalItems;
			}
			
			
			
			$records[] = (object) array (
				'id' 		=> $record->id,
				'from_id' 	=> $record->actor->id,
				'date' 	=> date_create($record->updated),
				'text' 	=> ($repost) ? $record->annotation : $record->object->content,
				'attachments' => $attachments,
				'owner' 	=> isset ($record->object->actor->id) ? $record->object->actor->id : $record->actor->id,
				'repost' 	=> $repost,
				'repost_text' 	=> ($repost) ? $record->object->content : null,
				'record_type' => !empty ($record->verb) ? $record->verb : 'text',
				'comments'	=> $comments,
				'likes'		=> $likes,
			);
			
			
		}
		
		return $records;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_Api_Comunity_Abstract::api()
	 */
	public function api ( $method=null, $params=array (), $sendMethod=SE_METHOD_GET )
	{
		$config = SocialApi_Config::get ();
		if (empty ($method)) {
			throw new SocialApi_Exception_Api (gettext ('Facebook Api - Method not specified'));
		}
		
		if ( empty ( $this->apiKey ))
		{
			$this->setApiKey ($config['Api']['Google']['apiKey']);
		}
		
		$_params = array (
				'key' 	=> $this->getApiKey (),
				'access_token' => $this->authObject->getAccessToken (),
		);
		$params = array_merge( $params, $_params );
		
		$http = new SocialApi_Http();
		switch (strtoupper($sendMethod))
		{
			case SE_METHOD_POST:
				$responce = $http->post ( $this->apiUrl . $method, $params );
				break;
			case SE_METHOD_PUT:
				$responce = $http->put ( $this->apiUrl . $method, $params );
				break;
			case SE_METHOD_DELETE:
				$responce = $http->delete ( $this->apiUrl . $method, $params );
				break;
			case SE_METHOD_GET:
			default:
				$responce = $http->get ( $this->apiUrl . $method, $params );
		}
		
		/*
		 *
		 * @TODO Добавить к Facebook поддержку сертификата
		 * 
		if (curl_errno($ch) == 60) { // CURLE_SSL_CACERT
			self::errorLog('Invalid or no certificate authority found, '.
					'using bundled information');
			curl_setopt($ch, CURLOPT_CAINFO,
					dirname(__FILE__) . '/fb_ca_chain_bundle.crt');
			$result = curl_exec($ch);
		}
		*/
		
		if ($responce) {
			$arr = json_decode ($responce);
			if (empty ($arr->error))
			{
				
				return $arr;
			}
			else 
			{
				throw new SocialApi_Exception_Api( $arr->error->code . " - " . $arr->error->message );
				return false;
			}
		}
		return false;
	}

	
	protected function modifyAttachment ($attachment, $owner_id, $date)
	{
		$type = (object) array ();
		switch ($attachment->objectType)
		{
			case 'article':
				if (preg_match ("#^events#is",$attachment->url))
				{
					$attachment->objectType = 'event';
					// Этим условием мы отделяем репосты от настоящих ссылок. Фейсбук - команда гениев, блеять!
					$type = (object) array (
							"url" 	=> $attachment->url,
							"name" => isset ($attachment->displayName) ? $attachment->displayName : null,
							"description"	=> isset ($attachment->content) ? $attachment->content : null,
							"image_src"		=> null,
					);
				}
				else 
				{
					$attachment->objectType = 'link';
					// Этим условием мы отделяем репосты от настоящих ссылок. Фейсбук - команда гениев, блеять!
					$type = (object) array (
							"url" 	=> $attachment->url,
							"title" => isset ($attachment->displayName) ? $attachment->displayName : null,
							"description"	=> isset ($attachment->content) ? $attachment->content : null,
							"image_src"		=> null,
					);
				}
				break;
			case 'audio':
				$type = (object) array (
						"id" 		=> null,
						"owner_id" 	=> null,
						"performer"	=> null,
						"title"		=> null,
						"duration"	=> null,
				);
				break;
			case 'video':
				$type = (object) array (
						"id" 		=> $attachment->id,
						"owner_id" 	=> $owner_id,
						"title"		=> $attachment->content,
						"duration"	=> $duration,
						"date"		=> date_create($date),
						"image_big"	=> $attachment->url->url,
						"access_key"=> $attachment->id,
						'source'	=> $attachment->embed->url,
						'embed'		=> $attachment->embed->url,
				);
				break;
			case 'photo':
				$type = (object) array (
						"id" 		=> md5 ($attachment->fullImage->url),
						"owner_id"	=> $owner_id,
						"src"		=> $attachment->fullImage->url,
						"text"		=> isset($attachment->displayName) ? $attachment->displayName : null,
						"date"		=> date_create($date),
						"access_key"=> md5 ($attachment->fullImage->url),
				);
				break;
		}
			
		
		return array (
			'type' => $attachment->objectType,
			$attachment->objectType => $type,
		);
	}


}