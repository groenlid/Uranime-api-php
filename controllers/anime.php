<?php
class Anime {


    /**
     * Returns a collection of anime sorted by Anime.id
     * Because of the size of the table, max amount is 25.
     */
    function get( $id = NULL )
    {
        // Check if :id equals null,
        // means we should return all the anime
        if($id == NULL)
        {
            // Need to fetch limit and/or offset
            extract($this->getLimitOffset( 10, 25, 0));

            $allAnime = R::findAll('anime', 'ORDER BY id DESC LIMIT :offset, :limit',
                array(
                    ':offset' => (int) $offset,
                    ':limit' => (int) $limit,
                )
            );
            return $allAnime;
        }

        // Load specific anime
        $anime = R::load('anime',$id);
        
        // If only one anime is requested, we add the relationships.
        // (Episodes, Synonyms, etc)
        
        // We use manual finding of episodes instead of redbeans relationship
        // manager to choose variable name.
        $episodes = R::find(
            'episodes',
            'anime_id = :anime_id ORDER BY aired DESC',
            array(
                'anime_id' => $id
                ));//$anime->ownEpisodes;
        
        // Finding anime synonyms
        $synonyms = R::find(
            'anime_synonyms',
            'anime_id = :anime_id',
            array(
                'anime_id' => $id
                )
            );

        // Find the tags assosiated with the anime
        $tags = R::getAll(
            'SELECT genre.id, name, description
            FROM genre, anime_genre
            WHERE anime_genre.anime_id = :anime_id
            AND anime_genre.genre_id = genre.id
            ',
            array(
                ':anime_id' => $id
                )
            );
        
        
        $auth = new Authenticate();

        // If the user is logged in..
        if($auth->__isAuthenticated())
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

        $anime->episodes = $episodes;
        $anime->synonyms = $synonyms;
        $anime->tags = $tags;
        return $anime;
    }

    protected function put($id = null, $request_data = null)
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
        if($changesDone != 0)
            R::exec(
                'UPDATE scrape_info 
                SET scrape_needed=1 
                WHERE anime_id=' . $anime['id']
            );

        // Return the updated anime with updated episodes
        return $this->get($id);

    }
    

    /**
     * Updates the seen status for the given user on a specific episode
     */
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
                $userseen->timestamp = date( TIMESTAMP_FORMAT, $time);
                
                R::store($userseen);

                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Update the watchlist timestamp for a given user and anime.
     * if null is specified as time, the record will be deleted
     */
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

    }

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
