<?php
class Users {
    
    protected function get( $id = null ) {
        
        /*
        if($id == null)
            $id = Authenticate::$loggedInAs;

        if($id == null)
            throw new RestException('401');*/

        // Find the anime the user has seen
        $userSeen = R::getAll(
            'SELECT episodes.anime_id as id, COUNT(*) as amountSeen, MAX(timestamp) as latesSeen,
            title, fanart, anime.image
            FROM user_episodes, episodes, anime
            WHERE user_id = :user_id
            AND episodes.id = user_episodes.episode_id
            AND anime.id = episodes.anime_id
            GROUP BY episodes.anime_id
            ',
            array(
                ':user_id' => $id
                )
            );

        $watchlist = R::getAll(
            'SELECT anime_id as id,title, fanart, image, time as watchlistSince
            FROM anime, user_watchlist
            WHERE user_watchlist.anime_id = anime.id
            AND user_watchlist.user_id = :user_id',
            array(
                'user_id' => $id
                ) 
            );
        
        $userAnime = array(
            'Library' => $userSeen,
            'Watchlist' => $watchlist
            );

        return $userAnime;
        
    }

}
