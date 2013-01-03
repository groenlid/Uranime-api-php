<?php
class Anime extends ActiveRecord\Model
{
    static $table_name = "anime";
    
    public function last_seen(){
        $episodeIDs = array();

        foreach($this->episode as $ep)
            array_push($episodeIDs,$ep->id);

        $lastSeen = \UserEpisode::all( 
            array(
                'select' => 'count(*) as amount, MAX(timestamp) as last, user_id, nick',
                'joins' => 'LEFT JOIN users u ON(user_id = u.id)',
                'group' => 'user_id',
                'limit' => 10,
                'order' => 'last DESC',
                'conditions' => array('episode_id in (?)', $episodeIDs),
                )
            );
        //print_r(\UserEpisode::connection()->last_query);
        $arr = array();


        foreach($lastSeen as $obj){
          $user = \User::find($obj->user_id);
          array_push(
            $arr, array_merge(
              $obj->to_array(),
              $user->to_array(
                array(
                  'except' => 'password',
                  'methods' => 'gravatar'
                  )
                )
              )
            );
        }
        return $arr;
    }

    // Relations
    static $has_many = array(
        array('episode', 'class_name' => 'Episode'),
        array('animegenre', 'class_name' => 'AnimeGenre'),
        array('synonym', 'class_name' => 'Synonym'),
        array('genre', 'class_name' => 'Genre', 'through' => 'animegenre')
    );
}
