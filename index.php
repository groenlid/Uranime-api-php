<?php
require_once './restler/restler.php';
require_once './controllers/anime.php';


$r = new Restler();
$r->addAPIClass('Anime');
$r->handle();
