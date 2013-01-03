<?php
class Episode extends ActiveRecord\Model
{
    static $table_name = "episodes";
    
    static $belongs_to = array(
        array('anime')
    );

    // Relations
    static $has_many = array(
      array('userepisode', 'class_name' => 'UserEpisode'),
      array('user', 'class_name' => 'User', 'through' => 'userepisode')
    );
}


