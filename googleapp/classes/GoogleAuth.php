<?php

class GoogleAuth 
{
	protected $db;
	protected $client;
	protected $oauth2;
	protected $calendar_service;
	protected $miscalendarios;
	
	public function __construct(DB $db = null, Google_Client $googleClient = null) 
	{
		$this->db = $db;
		$this->client = $googleClient;

		if ($this->client) 
		{
			$this->client->setClientId('217478432355-fkpsochrqnd6fr7mol6tpbhjld9g0m2d.apps.googleusercontent.com');
			$this->client->setClientSecret('vIHRsUy4rCiWb_lyoroq3zsu');
			$this->client->setRedirectUri('http://localhost/loginphp/index.php');
			$this->client->setScopes(array('https://www.googleapis.com/auth/userinfo.email',
											'https://www.googleapis.com/auth/userinfo.profile',
											'https://www.googleapis.com/auth/calendar'));
		
			$this->oauth2 = new Google_Service_Oauth2($this->client);
			//$this->calendar_service = new Google_CalendarService($this->client);
		}
	}

	public function isLoggedIn () 
	{
		return isset($_SESSION['access_token']);
	}

	public function getUser()
	{
		return $this->oauth2->userinfo->get();
	}

	public function getAuthUrl () 
	{
		return $this->client->createAuthUrl();
	}

	public function checkRedirectCode () 
	{
		if (isset($_GET['code'])) 
		{
			$this->client->authenticate($_GET['code']);

			$this->setToken($this->client->getAccessToken());
			
			$user = $this->oauth2->userinfo->get();
			$this->storeUserInfo($user);
			//$calendario = $this->calendar_service->calendars->get('primary');
			//print "<h1>Mi calendario principal</h1><pre>" . print_r($calendario, true) . "</pre>";
			return true;
		}
		return false;
	}

	public function setToken ($token) 
	{
		$_SESSION['access_token'] = $token;

		$this->client->setAccessToken($token);
	}

	public function logout () 
	{
		unset($_SESSION['access_token']);
	}

	protected function getPayload () 
	{
		$payload = $this->client->verifyIdToken()->getAttributes()['payload'];
		return $payload;
	}

	protected function storeUser ($payload) 
	{
		$sql = "
			INSERT INTO 
				google_users (google_id, email)
			VALUES 
				({$payload['id']}, '{$payload['email']}')
			ON DUPLICATE KEY UPDATE id = id";

		$this->db->query($sql);
	}

	protected function storeUserInfo ($user) 
	{		
		$sql = "
			INSERT INTO 
				google_users (google_id, email, firstName, lastName, fullName)
			VALUES 
				({$user['id']}, '{$user['email']}', '{$user['given_name']}', '{$user['family_name']}', '{$user['name']}')
			ON DUPLICATE KEY UPDATE id = id";

		$this->db->query($sql);
	}
}