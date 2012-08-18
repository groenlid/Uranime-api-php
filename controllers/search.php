<?php
class Search{

    public function get($q=""){
        
        $tag_id = isset($_GET['tag_id']) ? $_GET['tag_id'] : null;
        if($tag_id != null && !is_numeric($tag_id))
            throw new RestException(400, "Wrong format of tag_id. Should be int");

        else if($tag_id != null){
            $anime_genre = R::find(
                'anime_genre',
                'genre_id = :genre_id',
                array(
                    'genre_id' => $tag_id
                    )
                );
            $anime = array();
            foreach($anime_genre as $singleGenre)
            {
                $anime_id = $singleGenre['anime_id'];
                $anime[$anime_id] = R::load('anime',$anime_id);
                $anime[$anime_id]['tags'] = R::find(
                    'genre',
                    'genre.id = :genre_id',
                    array(
                        'genre_id' => $singleGenre['genre_id']
                    )
                );

                /* THIS FETCHES ALL THE TAGS FOR EACH ANIME.. TO BIG FOR ANDROID APP   
                R::getAll(
                    'SELECT genre.id, name, description
                    FROM genre, anime_genre
                    WHERE anime_genre.anime_id = :anime_id
                    AND anime_genre.genre_id = genre.id
                    ',
                    array(
                        ':anime_id' => $anime_id
                    )
                );
                 */

            }
            return $anime;
        }

        if(isset($_GET['q']))
            $q = strtolower(trim($_GET['q']));
        if(empty($q))
            throw new RestException(404,"No query given. Use ?q= to query");
        $synonyms = R::find(
            'anime_synonyms',
            'lower(title) LIKE ?',
            array(
                 '%'.$q.'%'
                )
            );

        $anime = array();
        foreach($synonyms as $synonym)
        {
            $anime_id = $synonym['anime_id'];
            if(array_key_exists($anime_id,$anime))
                continue;
            $anime[$anime_id] = R::load('anime',$anime_id);

            // we need to add the synonyms to the result list.
            $anime[$anime_id]['synonyms'] = R::find(
                'anime_synonyms',
                'anime_id = :anime_id',
                array(
                    'anime_id' => $anime_id
                    )
                );
        
        }

        return $anime;
    }

}

