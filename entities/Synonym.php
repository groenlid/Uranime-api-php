<?php
class Synonym extends ActiveRecord\Model
{
    static $table_name = "anime_synonyms";
    
    static $belongs_to = array(
        array('anime')
        );
}


