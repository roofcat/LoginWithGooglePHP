<?php

require_once 'googleapp/init.php';

$auth = new GoogleAuth();

$auth->logout();

header('Location: index.php');