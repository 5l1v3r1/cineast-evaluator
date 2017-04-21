<?php
use DBA\QueryFilter;
use DBA\Session;
use DBA\User;

/**
 * Handles the login sessions
 *
 * @author Sein
 */
class Login {
  private $user    = null;
  private $valid   = false;
  private $session = null;
  
  /**
   * Creates a Login-Instance and checks automatically if there is a session
   * running. It updates the session lifetime again up to the session limit.
   */
  public function __construct() {
    global $FACTORIES;
    
    $this->user = null;
    $this->session = null;
    $this->valid = false;
    if (isset($_COOKIE['session'])) {
      $session = $_COOKIE['session'];
      $filter1 = new QueryFilter(Session::SESSION_KEY, $session, "=");
      $filter2 = new QueryFilter(Session::IS_OPEN, "1", "=");
      $filter3 = new QueryFilter(Session::LAST_ACTION_DATE, time() - 10000, ">");
      $check = $FACTORIES::getSessionFactory()->filter(array($FACTORIES::FILTER => array($filter1, $filter2, $filter3)));
      if ($check === null || sizeof($check) == 0) {
        setcookie("session", "", time() - 600); //delete invalid or old cookie
        return;
      }
      $s = $check[0];
      $this->user = $FACTORIES::getUserFactory()->get($s->getUserId());
      if ($this->user !== null) {
        if ($s->getLastActionDate() < time() - $this->user->getSessionLifetime()) {
          setcookie("session", "", time() - 600); //delete invalid or old cookie
          return;
        }
        $this->valid = true;
        $this->session = $s;
        $s->setLastActionDate(time());
        $FACTORIES::getSessionFactory()->update($s);
        setcookie("session", $s->getSessionKey(), time() + $this->user->getSessionLifetime());
      }
    }
  }
  
  /**
   * This is a very dirty hack to allow importing of queries from commandline and just provide the username there
   *
   * @param $user User
   */
  public function overrideUser($user) {
    $this->user = $user;
  }
  
  /**
   * Returns true if the user currently is loggedin with a valid session
   */
  public function isLoggedin() {
    return $this->valid;
  }
  
  /**
   * Logs the current user out and closes his session
   */
  public function logout() {
    global $FACTORIES;
    
    $this->session->setIsOpen(0);
    $FACTORIES::getSessionFactory()->update($this->session);
    setcookie("session", "", time() - 600);
    $this->session = null;
    $this->valid = false;
    $this->user = null;
  }
  
  /**
   * Returns the uID of the currently logged in user, if the user is not logged
   * in, the uID will be -1
   */
  public function getUserID() {
    if (!$this->isLoggedin()) {
      return -1;
    }
    return $this->user->getId();
  }
  
  public function getUser() {
    if (!$this->isLoggedin()) {
      return null;
    }
    return $this->user;
  }
  
  /**
   * Executes a login with given username and password (plain)
   *
   * @param string $username username of the user to be logged in
   * @param string $password password which was entered on login form
   * @return true on success and false on failure
   */
  public function login($username, $password) {
    global $FACTORIES;
    
    if ($this->valid == true) {
      return false;
    }
    $filter = new QueryFilter(User::USERNAME, $username, "=");
    $check = $FACTORIES::getUserFactory()->filter(array($FACTORIES::FILTER => array($filter)));
    if ($check === null || sizeof($check) == 0) {
      return false;
    }
    $user = $check[0];
    if ($user->getIsValid() != 1) {
      return false;
    }
    else if (!Encryption::passwordVerify($password, $user->getPasswordSalt(), $user->getPasswordHash())) {
      return false;
    }
    $this->user = $user;
    $startTime = time();
    $s = new Session(0, $this->user->getId(), $startTime, $startTime, 1, $this->user->getSessionLifetime(), "");
    $s = $FACTORIES::getSessionFactory()->save($s);
    if ($s === null) {
      return false;
    }
    $sessionKey = Encryption::sessionHash($s->getId(), $startTime, $user->getEmail());
    $s->setSessionKey($sessionKey);
    $FACTORIES::getSessionFactory()->update($s);
    
    $this->user->setLastLoginDate(time());
    $FACTORIES::getUserFactory()->update($this->user);
    
    $this->valid = true;
    setcookie("session", "$sessionKey", time() + $this->user->getSessionLifetime());
    return true;
  }
}




