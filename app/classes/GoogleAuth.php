<?php

class GoogleAuth {

	protected $db;

	protected $client;

	protected $oauth2;

	public function __construct(DB $db = null, Google_Client $googleClient = null) {

		$this->db = $db;
		$this->client = $googleClient;

		if ($this->client) {
			$this->client->setClientId('217478432355-uvo0kr1iev4gov3tgjbeqrbig8gav7hf.apps.googleusercontent.com');
			$this->client->setClientSecret('8UP6WLroCs4WvovDkMISdnMK');
			$this->client->setRedirectUri('http://localhost/loginphp/index.php');
			$this->client->setScopes(array("https://www.googleapis.com/auth/userinfo.email",
											"https://www.googleapis.com/auth/userinfo.profile"));
		
			$this->oauth2 = new Google_Oauth2Service($this->client);
		}
	}

	public function isLoggedIn () {

		return isset($_SESSION['access_token']);
	}

	public function getAuthUrl () {

		return $this->client->createAuthUrl();
	}

	public function checkRedirectCode () {

		if (isset($_GET['code'])) {

			$this->client->authenticate($_GET['code']);

			$this->setToken($this->client->getAccessToken());
			
			$user = $this->oauth2->userinfo->get();
			$this->storeUser($user);

			return true;
		}

		return false;
	}

	public function setToken ($token) {

		$_SESSION['access_token'] = $token;

		$this->client->setAccessToken($token);
	}

	public function logout () {

		unset($_SESSION['access_token']);
	}

	protected function getPayload () {

		$payload = $this->client->verifyIdToken()->getAttributes()['payload'];
		return $payload;
	}

	protected function storeUser ($user) {
		
		$sql = "
		INSERT INTO google_users (google_id, email, firstName, lastName, fullName)
		VALUES ({$user['id']}, '{$user['email']}', '{$user['given_name']}', '{$user['family_name']}', '{$user['name']}')
		ON DUPLICATE KEY UPDATE id = id";

		$this->db->query($sql);
	}
}