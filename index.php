<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

// Vendors
require_once __DIR__.'/vendors/restler/vendor/restler.php';
require_once __DIR__.'/vendors/ActiveRecord/ActiveRecord.php';
require_once __DIR__.'/vendors/oauth2-server-php/src/OAuth2/Autoloader.php';

// Autoloader
OAuth2_Autoloader::register();

// Classes
require_once __DIR__.'/controllers/authenticate.php';
require_once __DIR__.'/controllers/anime.php';
require_once __DIR__.'/controllers/episodes.php';
require_once __DIR__.'/controllers/users.php';
require_once __DIR__.'/controllers/search.php';
require_once __DIR__.'/controllers/tags.php';

//require_once getcwd().'/rb.php';
//require_once getcwd().'/BeanExport.php';
require_once __DIR__.'/config/db.php';


// Entities
require_once __DIR__.'/entities/Anime.php';
require_once __DIR__.'/entities/UserEpisode.php';
require_once __DIR__.'/entities/Episode.php';
require_once __DIR__.'/entities/Genre.php';
require_once __DIR__.'/entities/AnimeGenre.php';
require_once __DIR__.'/entities/Synonym.php';
require_once __DIR__.'/entities/User.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');

use Luracast\Restler\Restler;

$r = new Restler();
$r->addAPIClass('Luracast\\Restler\\Resources');
$r->setSupportedFormats('JsonFormat');
//$r->addAuthenticationClass('Authenticate');
$r->addAPIClass('controllers\Anime');
$r->addAPIClass('controllers\Episodes');
//$r->addAPIClass('Users');
$r->addAPIClass('controllers\Search');
//$r->addAPIClass('Tags');
//$r->addAPIClass('Auth');
$r->handle();

