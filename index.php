<?php
require_once './restler/restler.php';
require_once './controllers/anime.php';
require_once './rb.php';
require_once './config/db.php';


$r = new Restler();
$r->addAPIClass('Anime');
$r->handle();

