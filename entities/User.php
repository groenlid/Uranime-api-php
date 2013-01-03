<?php
class User extends ActiveRecord\Model
{
    static $table_name = "users";

    static $has_many = array(
      array('userepisode', 'class_name' => 'UserEpisode'),
      array('episode', 'through' => 'userepisode')
    );

    function gravatar() {
      return \User::gravatar_url($this->email);
    }

    static function gravatar_url($email){
      return "http://www.gravatar.com/avatar/" . 
              md5( strtolower( trim( $email ) ) );
    }
}
