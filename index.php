<?php
require_once getcwd().'/vendors/restler/restler/restler.php';
require_once getcwd().'/controllers/authenticate.php';
require_once getcwd().'/controllers/anime.php';
require_once getcwd().'/controllers/episodes.php';
require_once getcwd().'/controllers/users.php';
require_once getcwd().'/controllers/search.php';
require_once getcwd().'/controllers/tags.php';
require_once getcwd().'/rb.php';
require_once getcwd().'/config/db.php';

header('Access-Control-Allow-Origin: *');

$r = new Restler();
$r->setSupportedFormats('JsonFormat','JsonpFormat');
$r->addAuthenticationClass('Authenticate');
$r->addAPIClass('Anime');
$r->addAPIClass('Episodes');
$r->addAPIClass('Users');
$r->addAPIClass('Search');
$r->addAPIClass('Tags');
$r->addAPIClass('Auth');
$r->handle();

