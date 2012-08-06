<?php
/**
 * Класс для доступа к АПИ социальной сети Facebook
 * @author suver
 *
 */
class SocialApi_Api_Comunity_Facebook extends SocialApi_Api_Comunity_Abstract {
	
	protected $apiUrl = 'https://graph.facebook.com/';
	
	protected $meInfo = null;
	protected $meId = null;
	protected $meName = null;
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_Api_Comunity_Abstract::me()
	 */
	public function me ()
	{
		$userInfo = $this->api ('me');
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
				"name" 		=> isset ($this->meInfo->name) ? $this->meInfo->name : "",
				"first_name" => isset ($this->meInfo->first_name) ? $this->meInfo->first_name : "",
				"last_name" => isset ($this->meInfo->last_name) ? $this->meInfo->last_name : "",
				"username" 	=> isset ($this->meInfo->username) ? $this->meInfo->username : "",
				"screen_name" => isset ($this->meInfo->username) ? $this->meInfo->username : "",
				"birthday" 	=> isset ($this->meInfo->birthday) ? $this->meInfo->birthday : "",
				"link" 		=> isset ($this->meInfo->link) ? $this->meInfo->link : "",
				"quotes" 	=> isset ($this->meInfo->quotes) ? $this->meInfo->quotes : "",
				//"email" 	=> isset ($this->meInfo->email) ? $this->meInfo->email : "",
				"gender" 	=> isset ($this->meInfo->gender) ? $this->meInfo->gender : "",
				"timezone" 	=> isset ($this->meInfo->timezone) ? $this->meInfo->timezone : "",
		);
		
		return (object) $info;
	}
	

	
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_Api_Comunity_Abstract::get()
	 */
	public function get ($objectId)
	{
		$userInfo = $this->api ($objectId);
		
		if (isset ($userInfo->birthday))
		{
			$bdate = explode ("/",$userInfo->birthday);
			$userInfo->birthday = $bdate['1'] . "." . $bdate['0'] . "." . $bdate['2'];
		}
		
		$info = array (
				"id" 		=> $userInfo->id,
				"name" 		=> isset ($userInfo->name) ? $userInfo->name : "",
				"first_name" => isset ($userInfo->first_name) ? $userInfo->first_name : "",
				"last_name" => isset ($userInfo->last_name) ? $userInfo->last_name : "",
				"username" 	=> isset ($userInfo->username) ? $userInfo->username : "",
				"screen_name" => isset ($userInfo->username) ? $userInfo->username : "",
				"birthday" 	=> isset ($userInfo->birthday) ? $userInfo->birthday : "",
				"link" 		=> isset ($userInfo->link) ? $userInfo->link : "",
				"quotes" 	=> isset ($userInfo->quotes) ? $userInfo->quotes : "",
				//"email" 	=> isset ($userInfo->email) ? $userInfo->email : "",
				"gender" 	=> isset ($userInfo->gender) ? $userInfo->gender : "",
				"timezone" 	=> isset ($userInfo->timezone) ? $userInfo->timezone : "",
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
		$posts = $this->api ($objectId.'/feed');
		
		$records = array ();
		foreach ($posts->data as $record)
		{
			if (is_object ($record) AND isset ($record->message))
			{
				$attachments = array ();
				if (isset ($record))
				{
					$type = $this->modifyAttachment ($record);
					$attachments[] = (object) array (
							'type' => $record->type,
							$record->type => $type,
					);
				}
			}
			
			// Корректируем значения полей
			$repost = false;
			$record = $this->modifyType ($record);
			
			// Заносим коментарии пользователей
			$comments = array ();
			if (isset($record->comments->data) AND is_array ($record->comments->data)) 
			{
				foreach ($record->comments->data as $comment)
				{
					if (is_object ($comment))
					$comments[] = array (
						'id' 	=> $comment->id,
						'from_id'	=> $comment->from->id,
						//'from_name'	=> $comment->from->name,
						'message'	=> $comment->message,
						'date'	=> date_create($comment->created_time),
					);
				}
			}
			
			// Заносим количество лайков
			$likes = 0;
			if (isset ($record->likes->count))
			{
				$likes = $record->likes->count;
			}
			
			$records[] = array (
				'id' 		=> $record->id,
				'from_id' 	=> $record->from->id,
				'date' 	=> date_create($record->updated_time),
				'text' 	=> isset ($record->message) ? $record->message : null,
				'attachments' => $attachments,
				'owner' 	=> $record->from->id,
				'repost' 	=> $repost,
				'repost_text' 	=> ($repost AND isset ($record->description)) ? $record->description : null,
				'record_type' => !empty ($record->type) ? $record->type : 'text',
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
		$objectId = ($objectId == 'me') ? 'me' : $objectId ;
		$posts = $this->api ('search',array (
			// Искать записи не раньше чем
			'until' => 'yesterday',
			// Последняя дата для поиска
			'since' => '',
			// Лимит выборки
			'limit' => 100,
			// Смещение 
			'offset'=> 0,
			// Строка поиска 
			'q'		=> $search,
		));
		
		$records = array ();
		foreach ($posts->data as $record)
		{
			if (is_object ($record) AND isset ($record->message))
			{
				$attachments = array ();
				if (isset ($record))
				{
					$type = $this->modifyAttachment ($record);
					$attachments[] = (object) array (
							'type' => $record->type,
							$record->type => $type,
					);
				}
			}
				
			// Корректируем значения полей
			$repost = false;
			$record = $this->modifyType ($record);
				
			// Заносим коментарии пользователей
			$comments = array ();
			if (isset($record->comments->data) AND is_array ($record->comments->data))
			{
				foreach ($record->comments->data as $comment)
				{
					if (is_object ($comment))
						$comments[] = array (
								'id' 	=> $comment->id,
								'from_id'	=> $comment->from->id,
								//'from_name'	=> $comment->from->name,
								'message'	=> $comment->message,
								'date'	=> date_create($comment->created_time),
						);
				}
			}
				
			// Заносим количество лайков
			$likes = 0;
			if (isset ($record->likes->count))
			{
				$likes = $record->likes->count;
			}
				
			$records[] = array (
					'id' 		=> $record->id,
					'from_id' 	=> $record->from->id,
					'date' 	=> date_create($record->updated_time),
					'text' 	=> isset ($record->message) ? $record->message : null,
					'attachments' => $attachments,
					'owner' 	=> $record->from->id,
					'repost' 	=> $repost,
					'repost_text' 	=> ($repost AND isset ($record->description)) ? $record->description : null,
					'record_type' => !empty ($record->type) ? $record->type : 'text',
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
		if (empty ($method)) {
			throw new SocialApi_Exception_Api (gettext ('Facebook Api - Method not specified'));
		}
		
		$_params = array (
				'access_token' 	=> $this->authObject->getAccessToken (),
				'app_id'		=> $this->authObject->getAppId (),
				'method'		=> strtolower($sendMethod),
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

	
	protected function modifyAttachment ($record)
	{
		$type = (object) array ();
		switch ($record->type)
		{
			case 'link':
				// Этим условием мы отделяем репосты от настоящих ссылок. Фейсбук - команда гениев, блеять!
				if (isset ($record->caption))
				{
					$type = (object) array (
							"url" 	=> $record->link,
							"title" => isset ($record->title) ? $record->title : null,
							"description"	=> isset ($record->description) ? $record->description : null,
							"image_src"		=> isset ($record->picture) ? $record->picture : null,
					);
				}
				break;
			case 'audio':
				if (isset ($record->object_id))
				{
					$type = (object) array (
							"id" 		=> null,
							"owner_id" 	=> null,
							"performer"	=> null,
							"title"		=> null,
							"duration"	=> null,
					);
				}
				else
				{
		
				}
				break;
			case 'video':
				// Если видео загрузили мы, то он есть.
				if (isset ($record->object_id))
				{
					$video = $this->api ($record->object_id);
					$duration = 0;
					if (isset ($record->properties))
					{
						foreach ($record->properties as $propertie)
						{
							if ($propertie->name == 'Length')
								$duration = $propertie->text;
						}
					}
					$type = (object) array (
							"id" 		=> $video->id,
							"owner_id" 	=> $video->from->id,
							"title"		=> $video->description,
							"duration"	=> $duration,
							"date"		=> date_create($video->updated_time),
							"image_big"	=> $video->format[1]->picture,
							"access_key"=> $video->id,
							'source'	=> $video->source,
							'embed'		=> $video->embed_html,
					);
		
				}
				// а если взяли с ютуба, то нет. Ахуительные стандарты фейсбука. Ахуительно удобное АПИ.
				else {
					$type = (object) array (
							"id" 		=> $record->id,
							"owner_id" 	=> $record->from->id,
							"title"		=> $record->description,
							"duration"	=> 0,
							"date"		=> date_create($record->updated_time),
							"image_big"	=> $record->picture,
							"access_key"=> $record->id,
							'source'	=> $record->source,
							'embed'		=> $record->link,
					);
				}
				break;
			case 'photo':
				$photo = $this->api ($record->object_id);
				if (is_object ($photo))
				{
					$type = (object) array (
							"id" 		=> $photo->id,
							"owner_id"	=> $photo->from->id,
							"src"		=> $photo->images['0']->source,
							"text"		=> isset($photo->name) ? $photo->name : null,
							"date"		=> date_create($photo->updated_time),
							"access_key"=> $photo->id,
					);
				}
				else {
					var_dump ($photo);
				}
				break;
		}
			
		
		return $type;
	}

	protected function modifyType ($record)
	{
		if (empty ($record->caption) AND isset ($record->type) AND ($record->type == 'link'))
		{
			$repost = true;
			$record->type = 'text';
		}
			
		if ($record->type == 'status')
		{
			$record->type = 'text';
		}
		else if (isset ($record->story))
		{
			$record->type = 'story';
			if (empty ($record->message))
				$record->message = $record->story;
		}
		return $record;
	}
}