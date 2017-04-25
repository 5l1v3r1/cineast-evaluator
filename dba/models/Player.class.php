<?php

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 02.01.17
 * Time: 23:57
 */

namespace DBA;

class Player extends AbstractModel {
  private $playerId;
  private $playerName;
  private $oauthId;
  private $firstLogin;
  private $lastLogin;
  
  function __construct($playerId, $playerName, $oauthId, $firstLogin, $lastLogin) {
    $this->playerId = $playerId;
    $this->playerName = $playerName;
    $this->oauthId = $oauthId;
    $this->firstLogin = $firstLogin;
    $this->lastLogin = $lastLogin;
  }
  
  function getKeyValueDict() {
    $dict = array();
    $dict['playerId'] = $this->playerId;
    $dict['playerName'] = $this->playerName;
    $dict['oauthId'] = $this->oauthId;
    $dict['firstLogin'] = $this->firstLogin;
    $dict['lastLogin'] = $this->lastLogin;
    
    return $dict;
  }
  
  function getPrimaryKey() {
    return "playerId";
  }
  
  function getPrimaryKeyValue() {
    return $this->playerId;
  }
  
  function getId() {
    return $this->playerId;
  }
  
  function setId($id) {
    $this->playerId = $id;
  }
  
  function getPlayerName(){
    return $this->playerName;
  }
  
  function setPlayerName($playerName){
    $this->playerName = $playerName;
  }
  
  function getOauthId(){
    return $this->oauthId;
  }
  
  function setOauthId($oauthId){
    $this->oauthId = $oauthId;
  }
  
  function getFirstLogin(){
    return $this->firstLogin;
  }
  
  function setFirstLogin($firstLogin){
    $this->firstLogin = $firstLogin;
  }
  
  function getLastLogin(){
    return $this->lastLogin;
  }
  
  function setLastLogin($lastLogin){
    $this->lastLogin = $lastLogin;
  }

  const PLAYER_ID = "playerId";
  const PLAYER_NAME = "playerName";
  const OAUTH_ID = "oauthId";
  const FIRST_LOGIN = "firstLogin";
  const LAST_LOGIN = "lastLogin";
}
