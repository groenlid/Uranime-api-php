<?php namespace controllers;
class Search{


    public function options($q = null, $tag = null) {
        header( 'Allow: GET,PUT,POST,DELETE,OPTIONS' );
    }

    /**
     * Searches for anime that matches the given query string or is associated with the given tag id
     * @param string q The search query. This has higher priority than tag id. Eg: bleach
	 * @param int tag A given tag id. Eg: 
	 **/
    public function get($q=null, $tag= null){
    	if($q == null && $tag == null)
			throw new \RestException(400,"Need to specify either a query (?q=) or a tag id (?tag=)");
		
		if($q != null && trim($q) != "")
		{
			$synonyms = \Synonym::find('all', 
				array(
					'conditions' => array(
						'lower(title) LIKE ?', 
						'%' . strtolower($q) . '%'
						)
					)
				);
			$ids = array();	
			$anime = array();
			
			foreach($synonyms as $a)
			{
				if(in_array($a->anime_id, $ids))
					continue;
				array_push($anime, $a->anime->to_array(array('include' => array('synonym'))));
				array_push($ids,$a->anime_id);				
			}
			
			return $anime;
        }
        else if($tag != null && is_numeric($tag))
        {
            $tag = \Genre::find($tag);
            $genre_anime = $tag->anime;
            $anime = array();
            foreach($genre_anime as $a)
                array_push($anime, $a->to_array());
            return $anime;
        }
		die();
		
    }
}
