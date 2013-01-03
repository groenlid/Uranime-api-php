<?php namespace controllers;
class Episodes {
    
    public function options($id) {
        header( 'Allow: GET,PUT,POST,DELETE,OPTIONS' );
    }

    function get($id)
    {
        // Load specific anime
        $episode = \Episode::find($id);
        return  $episode->to_array(array(
            'include' => array(
                'userepisode' => array(
                        'include' => array(
                            'user' => array(
                                'except' => 'password',
                                'methods' => 'gravatar'
                                )
                            )
                        )
            ),
            'except' => array(
                'user' => 'password'
            )
        ));
        // If the user is logged in..
    }   

}
