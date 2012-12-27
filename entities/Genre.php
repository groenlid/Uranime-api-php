<?php
class Genre extends ActiveRecord\Model
{
    static $table_name = "genre";
    
    static $has_many = array(
    	array('animegenre', 'class_name' => 'AnimeGenre'),
        array('anime', 'through' => 'animegenre')
        );
}


