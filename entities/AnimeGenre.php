<?php
class AnimeGenre extends ActiveRecord\Model
{
    static $table_name = "anime_genre";

    static $belongs_to = array(
        array('anime'),
        array('genre')
        );
    
}


