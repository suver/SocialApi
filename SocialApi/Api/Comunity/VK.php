<?php
/**
 * Класс для доступа к АПИ социальной сети Vkontakte
 * @author suver
 *
 */
class SocialApi_Api_Comunity_VK extends SocialApi_Api_Comunity_Abstract {
	
	protected $apiUrl = 'https://api.vk.com/method/';
	
	protected $meInfo = null; 
	protected $meId = null;
	protected $meName = null;
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_Api_Comunity_Abstract::me()
	 */
	public function me ()
	{
		$userShortInfo = $this->api ('getUserInfo');
		$userInfo = $this->api ('users.get', array (
				// перечисленные через запятую ID пользователей или их короткие имена (screen_name). Максимум 1000 пользователей.
				'uids' => $userShortInfo->response->user_id,
				// перечисленные через запятую поля анкет, необходимые для получения. 
				// Доступные значения: 
				//		uid, first_name, last_name, nickname, screen_name, sex, bdate (birthdate), 
				//		city, country, timezone, photo, photo_medium, photo_big, has_mobile, rate, 
				//		contacts, education, online, counters.
				'fields' => 'uid, first_name, last_name, nickname, screen_name, sex, bdate, city, country, timezone, counters,'
								.'photo, photo_medium, photo_big, has_mobile, rate, contacts, education, online, counters',
				// падеж для склонения имени и фамилии пользователя. Возможные значения: 
				// 		именительный – nom, 
				//		родительный – gen, 
				//		дательный – dat, 
				//		винительный – acc, 
				//		творительный – ins, 
				//		предложный – abl. 
				//	По умолчанию nom.
				'name_case' => 'nom',
		));
		$this->meInfo = $userInfo->response['0'];
		$this->meId = $this->meInfo->uid;
		$this->meName = $this->meInfo->first_name . " " . $this->meInfo->last_name;
		
		$this->meInfo->name = (isset ($this->meInfo->first_name)  AND isset ($this->meInfo->last_name)) 
											? $this->meInfo->first_name . " " . $this->meInfo->last_name : "" ;
		
		$info = array (
				"id" 		=> $this->meInfo->uid,
				"name" 		=> isset ($this->meInfo->name) ? $this->meInfo->name : "",
				"first_name" => isset ($this->meInfo->first_name) ? $this->meInfo->first_name : "",
				"last_name" => isset ($this->meInfo->last_name) ? $this->meInfo->last_name : "",
				"username" 	=> isset ($this->meInfo->nickname) ? $this->meInfo->nickname : "",
				"screen_name" => isset ($this->meInfo->screen_name) ? $this->meInfo->screen_name : "",
				"birthday" 	=> isset ($this->meInfo->bdate) ? $this->meInfo->bdate : "",
				"link" 		=> isset ($this->meInfo->screen_name) 
									? "http://vk.com/".$this->meInfo->screen_name : "http://vk.com/id".$this->meInfo->uid,
				"quotes" 	=> isset ($this->meInfo->quotes) ? $this->meInfo->quotes : "",
				//"email" 	=> isset ($this->meInfo->email) ? $this->meInfo->email : "",
				"gender" 	=> isset ($this->meInfo->sex) ? $this->meInfo->sex : "",
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
		$userInfo = $this->api ('users.get', array (
				// перечисленные через запятую ID пользователей или их короткие имена (screen_name). Максимум 1000 пользователей.
				'uids' => $objectId,
				// перечисленные через запятую поля анкет, необходимые для получения.
				// Доступные значения:
				//		uid, first_name, last_name, nickname, screen_name, sex, bdate (birthdate),
				//		city, country, timezone, photo, photo_medium, photo_big, has_mobile, rate,
				//		contacts, education, online, counters.
				'fields' => 'uid, first_name, last_name, nickname, screen_name, sex, bdate, city, country, timezone, counters,'
				.'photo, photo_medium, photo_big, has_mobile, rate, contacts, education, online, counters',
				// падеж для склонения имени и фамилии пользователя. Возможные значения:
				// 		именительный – nom,
				//		родительный – gen,
				//		дательный – dat,
				//		винительный – acc,
				//		творительный – ins,
				//		предложный – abl.
				//	По умолчанию nom.
				'name_case' => 'nom',
		));
		
		$userInfo->name = (isset ($userInfo->first_name)  AND isset ($userInfo->last_name))
		? $userInfo->first_name . " " . $userInfo->last_name : "" ;
		
		$info = array (
				"id" 		=> $userInfo->uid,
				"name" 		=> isset ($userInfo->name) ? $userInfo->name : "",
				"first_name" => isset ($userInfo->first_name) ? $userInfo->first_name : "",
				"last_name" => isset ($userInfo->last_name) ? $userInfo->last_name : "",
				"username" 	=> isset ($userInfo->nickname) ? $userInfo->nickname : "",
				"screen_name" => isset ($userInfo->screen_name) ? $userInfo->screen_name : "",
				"birthday" 	=> isset ($userInfo->bdate) ? $userInfo->bdate : "",
				"link" 		=> isset ($userInfo->screen_name)
				? "http://vk.com/".$userInfo->screen_name : "http://vk.com/id".$userInfo->uid,
				"quotes" 	=> isset ($userInfo->quotes) ? $userInfo->quotes : "",
				//"email" 	=> isset ($userInfo->email) ? $userInfo->email : "",
				"gender" 	=> isset ($userInfo->sex) ? $userInfo->sex : "",
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
		$objectId = ($objectId == 'me') ? 0 : $objectId ;
		$posts = $this->api ('wall.get', array (
				// идентификатор пользователя (по умолчанию - текущий пользователь).
				'owner_id' => $objectId,
				// смещение, необходимое для выборки определенного подмножества сообщений.
				'offset' => 0,
				// количество сообщений, которое необходимо получить (но не более 100).
				'count' => 100,
				// определяет, какие типы сообщений на стене необходимо получить. Возможны следующие значения параметра:
				//		owner - сообщения на стене от ее владельца
				//		others - сообщения на стене не от ее владельца
				//		all - все сообщения на стене
				//	Если параметр не задан, то считается, что он равен all.
				'filter' => 'all',
				// 1 - будут возвращены три массива wall, profiles, и groups. 
				//	По умолчанию дополнительные поля не возвращаются.
				'extended' => 1,
		));
		
		$records = array ();
		foreach ($posts->response->wall as $record)
		{
			if (is_object ($record))
			{
				$attachments = array ();
				if (is_array ($record->attachments))
				{
					foreach ($record->attachments as $attachment) 
					{
						$type = $this->modifyAttachment ($attachment);
						$attachments[] = (object) array (
								'type' => $attachment->type,
								$attachment->type => $type,
						);
					} 
				}
				
				// Заносим коментарии пользователей
				$comments = array ();
				if (isset($se->comments->count) AND ($se->comments->count > 0))
				{
					$comments = feedComment ( $record->id );
				}
				
				// Заносим количество лайков
				$likes = 0;
				if (isset ($record->likes->count))
				{
					$likes = $record->likes->count;
				}
				
				$records[] = (object)  array (
					'id' 		=> $record->id,
					'from_id' 	=> $record->from_id,
					'date' 		=> date_create('@'.$record->date),
					'text' 		=> !empty ($record->copy_owner_id) ? $record->copy_text : $record->text,
					'attachments' => $attachments,
					'owner' 	=> !empty ($record->copy_owner_id) ? $record->copy_owner_id : $record->from_id,
					'repost' 	=> !empty ($record->copy_owner_id) ? true : false,
					'repost_text' 	=> !empty ($record->copy_owner_id) ? $record->text : null,
					'record_type' => !empty ($record->media) ? $record->media->type : 'text',
					'comments'	=> $comments,
					'likes'		=> $likes,
				);
				
				
			}
		}
		
		return $records;
	}

	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_Api_Comunity_Abstract::search()
	 */
	public function search ($search)
	{
		$sea[] = (object) array ();
		$_search = $this->api ('newsfeed.search', array (
				// Поисковой запрос, по которому необходимо получить результаты.
				'q' => $search,
				// указывает, какое максимальное число записей следует возвращать, но не более 100.
				'count' => 100,
				// смещение, необходимое для выборки определенного подмножества результатов поиска.
				'offset' => 0,
				// время, в формате unixtime, начиная с которого следует получить новости для текущего пользователя. 
				//	Если параметр не задан, то он считается равным значению времени, которое было сутки назад.
				//'start_time' => 
				// время, в формате unixtime, до которого следует получить новости для текущего пользователя. 
				//	Если параметр не задан, то он считается равным текущему времени.
				//'end_time' => 
				// Строковый id последней полученной записи. (Возвращается в результатах запроса, для того, чтобы 
				//	исключить из выборки нового запроса уже полученные записи)
				//'start_id' => 
				// указывается 1 если необходимо получить информацию о пользователе или группе, разместившей запись. По умолчанию 0.
				'extended' => 1,
		));
		
		if (isset ($_search->response) AND is_array ($_search->response))
		{
			foreach ($_search->response as $se)
			{
				if (is_object ($se))
				{
					$attachments = array ();
					if (is_array ($record->attachments))
					{
						foreach ($record->attachments as $attachment)
						{
							$type = $this->modifyAttachment ($attachment);
							$attachments[] = (object) array (
									'type' => $attachment->type,
									$attachment->type => $type,
							);
						}
					}
					
					$comments = array ();
					if (isset($se->comments->count) AND ($se->comments->count > 0))
					{
						$comments = feedComment ( $se->id );
					}
					
					// Заносим количество лайков
					$likes = 0;
					if (isset ($record->likes->count))
					{
						$likes = $record->likes->count;
					}
					
					$sea[] = (object) array (
						'id'	=> $se->id,
						'date'	=> date_create('@'.$se->date),
						'owner_id'	=> $se->owner_id,
						'from_id'	=> $se->from_id,
						'text'	=> $se->text,
						'comments'	=> $comments,
						'likes'	=> $likes,
						'attachments'	=> $attachments,
					);
				}
			}
		}
		
		return (object) $sea;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see SocialApi_Api_Comunity_Abstract::api()
	 */
	public function api ( $method=null, $params=array (), $sendMethod=SE_METHOD_GET )
	{
		if (empty ($method)) {
			throw new SocialApi_Exception_Api (gettext ('VKApi - Method not specified'));
		}
		
		$_params = array (
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
		
		if ($responce) {
			$arr = json_decode ($responce);
			if (empty ($arr->error))
			{
				return $arr;
			}
			else 
			{
				throw new SocialApi_Exception_Api ( $arr->error->error_code . " - " . $arr->error->error_msg );
				return false;
			}
		}
		return false;
	}
	
	
	/**
	 * Получаем комментарии к записи новости/фида или поста
	 * @param unknown_type $objectId
	 */
	protected function feedComment ( $objectId )
	{
		// Заносим коментарии пользователей
		$comments = array ();
		
		$_coments = $this->api ('wall.getComments', array (
				// идентификатор пользователя, на чьей стене находится запись, к которой необходимо получить комментарии.
				//	Если параметр не задан, то он считается равным идентификатору текущего пользователя.
				'owner_id' => $objectId,
				// идентификатор записи на стене пользователя.
				'post_id' => $se->id,
				// 1 - будет возвращено дополнительное поле likes. По умолчанию поле likes не возвращается.
				'need_likes' => 1,
				// порядок сортировки комментариев:
				//		asc - хронологический
				//		desc - антихронологический
				'sort' => 100,
				// смещение, необходимое для выборки определенного подмножества комментариев.
				'offset' => 0,
				// количество комментариев, которое необходимо получить (но не более 100).
				'count' => 100,
				//Количество символов, по которому нужно обрезать комментарии.
				//	Укажите 0, если Вы не хотите обрезать комментарии. (по умолчанию 90).
				//	Обратите внимание, что комментарии обрезаются по словам.
				'preview_length' => 0,
					
		));
			
		if (isset ($_coments->response) AND is_array ($_coments->response->comment))
		{
			foreach ($_coments->response->comment as $comment)
			{
				if (is_object ($comment))
					$comments[] = array (
							'id' 	=> $comment->cid,
							'from_id'	=> $comment->uid,
							//'from_name'	=> null,
							'message'	=> $comment->text,
							'date'	=> date_create('@'.$comment->date),
					);
			}
		}
		return $comments;
	}
	
	
	protected function modifyAttachment ($attachment)
	{
		$type = (object) array ();
		switch ($attachment->type)
		{
			case 'link':
				$type = (object) array (
					"url" 	=> $attachment->link->url,
					"title" => $attachment->link->title,
					"description"	=> $attachment->link->description,
					"image_src"		=> $attachment->link->image_src,
				);
				break;
			case 'audio':
				$type = (object) array (
					"id" 		=> $attachment->audio->aid,
					"owner_id" 	=> $attachment->audio->owner_id,
					"performer"	=> $attachment->audio->performer,
					"title"		=> $attachment->audio->title,
					"duration"	=> $attachment->audio->duration,
				);
				break;
			case 'video':
				$type = (object) array (
					"id" 		=> $attachment->video->vid,
					"owner_id" 	=> $attachment->video->owner_id,
					"title"		=> $attachment->link->video,
					"duration"	=> $attachment->video->duration,
					"date"		=> date_create('@'.$attachment->video->date),
					"image_big"	=> $attachment->video->image_big,
					"access_key"	=> $attachment->video->access_key,
					"source"	=> '', // Нужно айти способ вставить ссылку на видео
					"embed"		=> '', // Нужно нйти способ вставить код видео
				);
				break;
			case 'photo':
				$type = (object) array (
					"id" 		=> $attachment->photo->pid,
					"owner_id"	=> $attachment->photo->owner_id,
					"src"		=> $attachment->photo->src_big,
					"text"		=> $attachment->photo->text,
					"date"		=> date_create('@'.$attachment->photo->created),
					"access_key"=> $attachment->photo->access_key,
				);
				break;
		}

		return $type;
	}
}