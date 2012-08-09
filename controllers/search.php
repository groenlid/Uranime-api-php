<?php
class Search{

    public function get($q=""){
        $q = strtolower(trim($_GET['q']));
        if(empty($q))
            throw new RestlerException(404,"No query given. Use ?q= to query");
        $synonyms = R::find(
            'anime_synonyms',
            'title LIKE ?',
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

