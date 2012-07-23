<?php
class Anime {
    function hello($to='world')
    {
        return "Hello $to!";
    }
    

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

        // The list is too big for all the animes
        // We need to require the user to give an id

        $anime = R::load('anime',$id);

        return $anime;
    }

    /**
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
