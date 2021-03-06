<?php
use DBA\Player;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 25.04.17
 * Time: 15:31
 */
class EveryDayLevel3Achievement extends GameAchievement {
  
  /**
   * @return string
   */
  function getAchievementName() {
    return "Every day a game Level 3";
  }
  
  /**
   * @param $player Player
   * @return bool
   */
  function isReachedByPlayer($player) {
    if ($player == null || $this->alreadyReached($player)) {
      return false;
    }
    
    $count = $this->getMaxDaysInRow($player);
    if ($count >= 7) {
      return true;
    }
    
    return false;
  }
  
  /**
   * @return string
   */
  function getAchievementImage() {
    return "success.png"; // TODO: add image
  }
  
  /**
   * @return float
   */
  function getMultiplicatorGain() {
    return 1.05;
  }
  
  /**
   * @return string
   */
  function getIdentifier() {
    return "gameDayLevel3";
  }
  
  /**
   * @return string
   */
  function getDescription() {
    return "Play at least one game per day for one week in row.<br>Gives 5% extra score";
  }
  
  /**
   * @param $player Player
   * @return int progress in %
   */
  function getProgress($player) {
    if ($player == null) {
      return 0;
    }
    return floor(min(100, $this->getMaxDaysInRow($player) / 7 * 100));
  }
}