<?php
return array (
	'oAuth' => array (
		'VK' => array (
			'appId' 	=> '3022354',
			'appSecret' => 'LpHYgopF9QvAkAj84WLC',
			'scope' 	=> 'notify,wall,groups,offline,photos,audio,video,friends,status,pages,messages',
			'redirect_uri' 	=> 'http://api.vk.com/blank.html',
			'response_type' =>'token',
			'access_token' 	=> '101047ad10ce992010ce99202a10e08732110cf10cbadbe9e61657ff9b233ba',
		),
		'Facebook' => array (
			'appId' 	=> '100105556802955',
			'appSecret' => '2b2f045d025f0168e9394ec5313c3ff5',
			'scope' 	=> 'publish_stream,user_photos,publish_actions,photo_upload,email,status_update,create_note,create_event,manage_pages,publish_actions,share_item,video_upload',
			'redirect_uri' 	=> 'http://VileElvis.dev/examples/SocialApi/Facebook/auth.php',
			'access_token' 	=> '101047ad10ce992010ce99202a10e08732110cf10cbadbe9e61657ff9b233ba',
			// В случае авторизации как девайс. Supported types: web_server, user_agent, client_cred, username
			// 'type'		=> 'web_server',
		),
		'Google' => array (
			'client_id' 	=> '674428709984-666ap1jdh0ovvv09siunh2qpeitmruts.apps.googleusercontent.com',
			'client_secret' => 'MVxLgwJC7gBfc5P8OND3vcIB',
			'scope' 	=> 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email',
			'redirect_uri' 	=> 'http://VileElvis.dev/examples/SocialApi/Google/auth.php',
			// Тип полученного токена. Этот параметр определяет время действия токена.
			// 		online - Только во время присутсвия пользователя на сайте, 
			//		offline - в любое время, даже когда пользователь покинул приложение
			'access_type'	=> 'online',
			// 		auto -   
			// 		force - 
			'approval_prompt' => 'force',
			//'access_token' 	=> '101047ad10ce992010ce99202a10e08732110cf10cbadbe9e61657ff9b233ba',
			'apiKey'		=> 'AIzaSyCSHFb-pYysVUIRdVvCqyJJ-PKwcGERKJw',
			'response_type' => 'token',
		),
	),
	'Api' => array (
		'Google' => array (
			'apiKey'		=> 'AIzaSyC5tq1px-GgE7eWizfk8bClrPvwXj9t2I4',
		),
	),
);