<?php
class Authenticator  {

    const Salt = "!*2-93V<.";

    private $_credentials;

    private $_error_messages;

    private $_userid;

    /**
     * Authenticator constructor.
     * @param $credentials array containing user credentials
     */
    public function __construct($credentials)
    {
        $this->_credentials = array(
            'username' => '',
            'password' => ''
        );

        if ($credentials) {
            /* union of $credentials + $this->_credentials */
            $this->_credentials = $credentials + $this->_credentials;
        }
    }

    /**
     * This method is called and determines if a user exists. If the user does not exist, it will append an error message to the errors array.
     */
    private function checkUser()
    {
        $pdo = Registry::getConnection();
        $query = $pdo->prepare("SELECT * FROM users WHERE binary username=:user AND password=:password LIMIT 1");
        $query->bindValue(":user",$this->_credentials['username']);
        $query->bindValue(":password", hash('sha256', $this->_credentials["password"] . Authenticator::Salt ));  //sha256 alogorithm
        $query->execute();
        // if user was not found
        if ($query->rowCount()!=1)
        {
            $this->_error_messages[] = "Username or password incorrect!";
        }
        $data = $query->fetch();
        $this->_userid = $data["ID"];

    }

    /**
     * @return mixed returns an array of errors that might have occurred during login process
     */
    public function getErrors() {
        return $this->_error_messages;
    }

    /**
     * @return bool returns true if the authentication was successful and false otherwise
     */
    public function login() {
        $this->checkUser();
        // if no errors are found
        if(empty($this->_error_messages))
        {
            // start a new session
            session_start();
            @session_regenerate_id (true);

            $_SESSION['username'] 	= $this->_credentials["username"];
            $_SESSION['user_id'] 	= $this->_userid;
            $_SESSION['http'] 		= md5($_SERVER['HTTP_USER_AGENT']);
            $_SESSION['start'] 		= time(); // taking now logged in time
            return true;
        }
        return false;

    }

}

?>