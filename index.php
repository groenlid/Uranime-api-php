<?php
require_once './vendor/restler/restler/restler.php';
require_once './controllers/authenticate.php';
require_once './controllers/anime.php';
require_once './controllers/episodes.php';
require_once './controllers/users.php';
require_once './controllers/search.php';
require_once './rb.php';
require_once './config/db.php';

header('Access-Control-Allow-Origin: *');

$r = new Restler();
$r->setSupportedFormats('JsonFormat','JsonpFormat');
$r->addAuthenticationClass('Authenticate');
$r->addAPIClass('Anime');
$r->addAPIClass('Episodes');
$r->addAPIClass('Users');
$r->addAPIClass('Search');
$r->addAPIClass('Auth');
$r->handle();

