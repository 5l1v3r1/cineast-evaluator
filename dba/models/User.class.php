<?php

namespace DBA;

class User extends AbstractModel {
  private $userId;
  private $username;
  private $email;
  private $passwordHash;
  private $passwordSalt;
  private $isValid;
  private $isComputedPassword;
  private $lastLoginDate;
  private $registeredSince;
  private $sessionLifetime;
  
  function __construct($userId, $username, $email, $passwordHash, $passwordSalt, $isValid, $isComputedPassword, $lastLoginDate, $registeredSince, $sessionLifetime) {
    $this->userId = $userId;
    $this->username = $username;
    $this->email = $email;
    $this->passwordHash = $passwordHash;
    $this->passwordSalt = $passwordSalt;
    $this->isValid = $isValid;
    $this->isComputedPassword = $isComputedPassword;
    $this->lastLoginDate = $lastLoginDate;
    $this->registeredSince = $registeredSince;
    $this->sessionLifetime = $sessionLifetime;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['userId'] = $this->userId;
    $dict['username'] = $this->username;
    $dict['email'] = $this->email;
    $dict['passwordHash'] = $this->passwordHash;
    $dict['passwordSalt'] = $this->passwordSalt;
    $dict['isValid'] = $this->isValid;
    $dict['isComputedPassword'] = $this->isComputedPassword;
    $dict['lastLoginDate'] = $this->lastLoginDate;
    $dict['registeredSince'] = $this->registeredSince;
    $dict['sessionLifetime'] = $this->sessionLifetime;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "userId";
  }
  
  function getPrimaryKeyValue() {
    return $this->userId;
  }
  
  function getId() {
    return $this->userId;
  }
  
  function setId($id) {
    $this->userId = $id;
  }
  
  function getUsername() {
    return $this->username;
  }
  
  function setUsername($username) {
    $this->username = $username;
  }
  
  function getEmail() {
    return $this->email;
  }
  
  function setEmail($email) {
    $this->email = $email;
  }
  
  function getPasswordHash() {
    return $this->passwordHash;
  }
  
  function setPasswordHash($passwordHash) {
    $this->passwordHash = $passwordHash;
  }
  
  function getPasswordSalt() {
    return $this->passwordSalt;
  }
  
  function setPasswordSalt($passwordSalt) {
    $this->passwordSalt = $passwordSalt;
  }
  
  function getIsValid() {
    return $this->isValid;
  }
  
  function setIsValid($isValid) {
    $this->isValid = $isValid;
  }
  
  function getIsComputedPassword() {
    return $this->isComputedPassword;
  }
  
  function setIsComputedPassword($isComputedPassword) {
    $this->isComputedPassword = $isComputedPassword;
  }
  
  function getLastLoginDate() {
    return $this->lastLoginDate;
  }
  
  function setLastLoginDate($lastLoginDate) {
    $this->lastLoginDate = $lastLoginDate;
  }
  
  function getRegisteredSince() {
    return $this->registeredSince;
  }
  
  function setRegisteredSince($registeredSince) {
    $this->registeredSince = $registeredSince;
  }
  
  function getSessionLifetime() {
    return $this->sessionLifetime;
  }
  
  function setSessionLifetime($sessionLifetime) {
    $this->sessionLifetime = $sessionLifetime;
  }
  
  const USER_ID              = "userId";
  const USERNAME             = "username";
  const EMAIL                = "email";
  const PASSWORD_HASH        = "passwordHash";
  const PASSWORD_SALT        = "passwordSalt";
  const IS_VALID             = "isValid";
  const IS_COMPUTED_PASSWORD = "isComputedPassword";
  const LAST_LOGIN_DATE      = "lastLoginDate";
  const REGISTERED_SINCE     = "registeredSince";
  const SESSION_LIFETIME     = "sessionLifetime";
}
