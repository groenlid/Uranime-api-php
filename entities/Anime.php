<?php
class Anime extends ActiveRecord\Model
{
    static $table_name = "anime";
    
    // Relations
    static $has_many = array(
        array('episode', 'class_name' => 'Episode'),
        array('animegenre', 'class_name' => 'AnimeGenre'),
        array('synonym', 'class_name' => 'Synonym'),
        array('genre', 'class_name' => 'Genre', 'through' => 'animegenre')
    );
}
