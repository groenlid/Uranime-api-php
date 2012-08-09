<?php
class Authenticate implements iAuthenticate {
    
    public static $loggedInAs = null;
    public $email = "a";
    function __isAuthenticated() 
    {

        $headers = apache_request_headers();
        if(!isset($headers['Authorization']))
            return FALSE;
        $auth = $headers['Authorization'];

        $authparams = explode(':',$auth);

        // TODO: mysql_real_escape_string email and passkey

        $email = "";
        for($i = 0; $i < count($authparams)-1; $i++)
            $email .= $authparams[$i];
        $this->email = $email;

        $passkey = $authparams[count($authparams)-1];
        
        // The authentication should use
        // email:sha1(sha1(salt+password) + time + email)

        // Check if email exists
        $user = R::findOne(
            'users',
            'email = :email',
            array(
                'email' => $email
            )
        );

        // If the user do not exist
        if($user == null)
            return FALSE;
    
        
        //$serverPasskey = sha1($user->password . (int)(time() / 60) . $user->email);
        $serverPasskey = sha1($user->password . $user->email);
        //echo $user->password . $user->email;
        if($user->email == $email && $passkey == $serverPasskey)
        {
            Authenticate::$loggedInAs = $user->id;
            return TRUE;
        }

        return FALSE;
    }

    function loggedInAs()
    {
        //if(is_null(Authenticate::$loggedInAs))
        //    $this->__isAuthenticated();
        return Authenticate::$loggedInAs;
    }
}
class Auth {
    public function check()
    {
        $auth = new Authenticate();
        if($auth->__isAuthenticated())
            return array('id' => Authenticate::$loggedInAs); //array('id' => $this->loggedInAs());
        else
            throw new RestException(401, 'Unauthorized. Bad credentials.');
    }

    
}
