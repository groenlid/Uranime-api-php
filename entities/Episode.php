<?php
class Episode extends ActiveRecord\Model
{
    static $table_name = "episodes";
    
    static $belongs_to = array(
        array('anime')
        );
}


