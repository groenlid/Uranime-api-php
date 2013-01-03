<?php namespace controllers;
class Anime {

    public function options($id) {
        header( 'Allow: GET,PUT,POST,DELETE,OPTIONS' );
    }

    /**
     * Returns the anime with the given id
     * @param int $id The anime id
	 * @return Anime anime
     */
    public function get($id)
    {
        // Load specific anime
        $anime = \Anime::find($id);

        
        

        //print_r($lastSeen);
        $ret = $anime->to_array(array(
            'include' => array(
                'episode',
                'genre',
                'synonym'
            ),
            'methods' => 'last_seen'
        ));
        //$ret['activity'] = $lastSeen;
        return $ret;

        /*
        // If only one anime is requested, we add the relationships.
        // (Episodes, Synonyms, etc)
        
        // We use manual finding of episodes instead of redbeans relationship
        // manager to choose variable name.
        
        $auth = new Authenticate();

        // If the user is logged in..
        if($auth->__isAllowed())
        {
            $userid = Authenticate::$loggedInAs;
            
            // .. we need to check if the anime is
            // in the user's watchlist
            $watchlist = R::findOne(
                'user_watchlist',
                'anime_id = :anime_id AND 
                user_id = :user_id',
                array(
                    'anime_id' => $id,
                    'user_id' => $userid
                    )    
                );
            $anime['watchlist'] = ($watchlist == null) ? null : $watchlist['time'];

            // .. then we check if the user has seen
            // any of the episodes in the series
            foreach($episodes as $episode)
            {
                $user_episode = R::findOne(
                    'user_episodes',
                    'user_id = :user_id AND 
                    episode_id = :episode_id',
                    array(
                        'user_id' => $userid,
                        'episode_id' => $episode->id
                        )
                    );
                
                $episode['seen'] = ($user_episode == null) ? null : $user_episode['timestamp'];
            }
        }

        $anime = $anime->export();
        $anime['episodes'] = R::exportAll($episodes);
        $anime['synonyms'] = R::exportAll($synonyms);
        $anime['tags'] = $tags;

        return $anime;*/
    }

    /**
     * Returns a collection of anime sorted by Anime.id
     * Because of the size of the table, max amount is 25.
     */
    function index($limit = 10, $offset = 0)
    {
        // Need to fetch limit and/or offset
        //extract($this->getLimitOffset( 10, 25, 0));
		$options = array(
			'limit' => $limit,
			'offset' => $offset
		);
		
		$anime = array();
		
		$anime_result = \Anime::all($options);
		foreach($anime_result as $a)
			array_push($anime,$a->to_array());
		return $anime;
    }
/*
    public function put($id = null, $request_data = null)
    {
        // Fetch the request_data
        
        if(is_null($id) || !is_numeric($id))
        {
            $id = (isset($request_data['id'])) ? $request_data : null;
            if(is_null($id) || !is_numeric($id))
                throw new RestException(400, 'No id given');
        }
        if(is_null($request_data) || empty($request_data))
            throw new RestException(400, 'No input data was given!');
            
        // Check if the anime exists & find the userid
        $anime = R::load('anime',$id);
        
        // Check if the user is logged in
        $auth = new Authenticate();

        // If the user is logged in..
        if(!$auth->__isAuthenticated())
        {
            // Check if the user wants to update the scrape for the anime
            if(isset($request_data['update']))
                $this->setScrape($id,$anime);
            $this->get($id);
        }

        

        $userid = Authenticate::$loggedInAs;


        if(is_null($anime) || !$anime->id)
            throw new RestException(404, 'The anime with id ' . $id . ' does not exist.');

        $changesDone = 0;

        // Update the watchlist timestamp status
        $updatedWatchlist = $this->updateWatchlist($request_data, $userid, $id);

        $changesDone += ($updatedWatchlist) ? 1 : 0 ;

        // Update the watched status for the episodes
        if(array_key_exists('episodes',$request_data))
            foreach($request_data['episodes'] as $episodeid => $episode)
                $changesDone += ($this->updateEpisode($episode, $userid, $episodeid) ? 1 : 0);

        // Update the scrape for the anime
        if($changesDone != 0 || isset($request_data['update']))
             $this->setScrape($id, $anime);

        // Return the updated anime with updated episodes
        return $this->get($id);

    }

    private function setScrape($anime_id, $anime_db_object)
    {
        if(!is_numeric($anime_id))
            throw new RestException(
                400, 
                'Anime id is of the wrong type. Should be integer'
            );
        
        $anime  = ($anime_db_object == null) 
            ? R::load('anime',$anime_id) 
            : $anime_db_object;
        
        if(!isset($anime['status']) || $anime['status'] != 'finished')
            return R::exec(
                'UPDATE scrape_info
                SET scrape_needed=1
                WHERE anime_id=' . $anime['id']
            );

        return false;
    }
    
*/
    /**
     * Updates the seen status for the given user on a specific episode
     *//*
    private function updateEpisode($episode, $userid, $id)
    {

        // Check if episode id is given
        if( ( empty( $episode['id'] ) && !is_numeric( $episode['id'] ) )
            && ( empty( $id ) && !is_numeric( $id ) ) )
            throw new RestException(400, "No episode-id is given");

        if( empty( $id ) || !is_numeric( $id  ) )
            $id = null;

        
        if(is_numeric($episode['id']) && !empty($episode['id']))
            $id = $episode['id'];
            
        // Check if episode exists
        
        $episode_in_db = R::load('episodes',$id);
        
        
        if(!$episode_in_db->id)
            throw new RestException(404,"No episode with id " . $id . " exists");

        // The user can only change the column seen ( No admin privileges yet ).

        if(array_key_exists('seen',$episode))
        {
            $seen = $episode['seen'];
            
            $userseen = R::findOne(
                'user_episodes',
                'user_id = :userid AND episode_id = :episodeid',
                array(
                    'userid' => $userid,
                    'episodeid' => $id
                    )
                );

            // Delete the record
            if(is_null($seen))
            {
                if(!is_null($userseen))
                    R::trash($userseen);
                return TRUE;
            }
            
            // Create/Update the record
            else 
            {
                if(is_null($userseen))
                {
                    $userseen = R::dispense('user_episodes');
                    $userseen->user_id = $userid;
                    $userseen->episode_id = $id;
                }

                $time = strtotime($seen);
                $userseen->timestamp = date( TIMESTAMP_FORMAT, time() );//date( TIMESTAMP_FORMAT, $time);
                
                R::store($userseen);

                return TRUE;
            }
        }
        return FALSE;
    }
*/
    /**
     * Update the watchlist timestamp for a given user and anime.
     * if null is specified as time, the record will be deleted
     *//*
    private function updateWatchlist($request_data, $userid, $animeid)
    {
        // Check if the user sends a new value for the user watchlist 
        if(array_key_exists('watchlist',$request_data))
        {
            $watchlist = R::findOne(
                'user_watchlist',
                'user_id = :user_id AND anime_id = :anime_id',
                array(
                    'user_id' => $userid,
                    'anime_id' => $animeid
                    )
                );

            // Should we delete or create/update the time
            if(is_null($request_data['watchlist']))
            {
                if(!is_null($watchlist))
                {
                    R::trash($watchlist);
                    return TRUE;
                }
            }
            else 
            {
                if(is_null($watchlist))
                {
                    $watchlist = R::dispense('user_watchlist');
                    $watchlist->user_id = $userid;
                    $watchlist->anime_id = $animeid;
                }
                $time = strtotime($request_data['watchlist']);
                $watchlist->time = date(TIMESTAMP_FORMAT, $time);
                $newID = R::store($watchlist);
            
                return TRUE;
            }

            return FALSE;
        }

    }*/

    /*
     * $defaultLimit: if limit is required in the url.
     *                if none are given, $default is used.
     */
    private function getLimitOffset($defaultLimit = null, $maxLimit = null, $minLimit = null)
    {
        $nan = new RestException(412, 'Limit or offset is not a valid number');
        $limit = isset($_GET['limit']) ? $_GET['limit'] : null;
        $offset = isset($_GET['offset']) ? $_GET['offset'] : null;
        
        if(($limit != null && !is_numeric($limit))
        || ($offset != null && !is_numeric($offset)))
            throw $nan;

        // If no limit is set in url but is required.
        if($defaultLimit != null)
            $limit = ($limit == null) ? $defaultLimit : $limit;

        if($limit < $minLimit) $limit = $minLimit;
        if($limit > $maxLimit) $limit = $maxLimit;

        return array('limit' => (int)$limit, 'offset' => (int)$offset);
    }
}
