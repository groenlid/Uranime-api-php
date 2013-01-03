<?php
class UserEpisode extends ActiveRecord\Model
{
  
    static $table_name = "user_episodes";

    static $belongs_to = array(
        array('user'),
        array('episode')
    );
    
}


