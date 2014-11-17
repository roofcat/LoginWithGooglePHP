<?php

require_once 'googleapp/init.php';

$db = new DB;
$googleClient = new Google_Client;

$auth = new GoogleAuth($db, $googleClient);

if ($auth->checkRedirectCode()) 
{
	header('Location: index.php');
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Sign In with Google with PHP</title>
</head>
<body>
	<?php if (!$auth->isLoggedIn()): ?>
		<a href="<?php echo $auth->getAuthUrl(); ?>">Sign In with Google</a>
	<?php else: ?>
		You are signed in. <a href="logout.php">Sign out</a>
	<?php endif; ?>
</body>
</html>