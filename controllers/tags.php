<?php
class Tags
{

    /**
     * Get all the genres from db
     */
    public function get(){
        $genre = R::find(
            'genre',
            'is_genre IS NOT NULL'
        ); 

        return $genre;

    }

}
