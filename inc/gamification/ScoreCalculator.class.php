<?php
use DBA\Achievement;
use DBA\AnswerSession;
use DBA\QueryFilter;
use DBA\TwoCompareAnswer;

/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 25.04.17
 * Time: 14:21
 */
class ScoreCalculator {
  private $answerSession;
  
  const SCORE_BASE           = "baseScore";
  const SCORE_TOTAL          = "totalScore";
  const SCORE_MULTIPLICATORS = "multiplicators";
  const SCORE_MULTIPLICATOR  = "multiplicator";
  
  const GAUSSIAN_CONST_MULT     = 1.1;
  const CONST_MULT_ADD          = 0.15;
  const GAUSSIAN_CONST_MULT_ADD = 0.16;
  const BASIC_CONST_MULT        = 1.1;
  
  /**
   * ScoreCalculator constructor.
   * @param $answerSession AnswerSession
   */
  public function __construct($answerSession) {
    $this->answerSession = $answerSession;
  }
  
  /**
   * @return int[]
   */
  public function getScore() {
    global $FACTORIES;
    
    $totalScore = array();
    
    $qF = new QueryFilter(TwoCompareAnswer::ANSWER_SESSION_ID, $this->answerSession->getId(), "=");
    $answers = $FACTORIES::getTwoCompareAnswerFactory()->filter(array($FACTORIES::FILTER => $qF));
    
    $score = sizeof($answers) * $this->answerSession->getCurrentValidity();
    $count = 0;
    foreach ($answers as $answer) {
      $tuple = $FACTORIES::getResultTupleFactory()->get($answer->getResultTupleId());
      $gaussian = new SimpleGauss($tuple, $this->answerSession);
      $count++;
      if ($gaussian->isValid()) {
        $score *= ScoreCalculator::GAUSSIAN_CONST_MULT + $count / sizeof($answers) * ScoreCalculator::GAUSSIAN_CONST_MULT_ADD - $tuple->getSigma() + min(5, $gaussian->getProbability($answer->getAnswer()));
      }
      else {
        $score *= ScoreCalculator::BASIC_CONST_MULT + $count / sizeof($answers) * ScoreCalculator::CONST_MULT_ADD;
      }
    }
    
    $score = floor($score/100);
    $multiplicators = array();
    $multiplication = 1;
    
    $totalScore[ScoreCalculator::SCORE_BASE] = $score;
    
    $achievementTester = new AchievementTester();
    
    // add score for achievements
    if ($this->answerSession->getPlayerId() != null) {
      $qF = new QueryFilter(Achievement::PLAYER_ID, $this->answerSession->getPlayerId(), "=");
      $achievements = $FACTORIES::getAchievementFactory()->filter(array($FACTORIES::FILTER => $qF));
      foreach ($achievements as $achievement) {
        $gameAchievement = $achievementTester->getAchievement($achievement->getAchievementName());
        if ($gameAchievement != null) {
          $score *= $gameAchievement->getMultiplicatorGain(); // apply the gain for the received achievements
          $multiplication *= $gameAchievement->getMultiplicatorGain();
        }
      }
    }
    
    $totalScore[ScoreCalculator::SCORE_TOTAL] = floor($score);
    $totalScore[ScoreCalculator::SCORE_MULTIPLICATORS] = $multiplicators;
    $totalScore[ScoreCalculator::SCORE_MULTIPLICATOR] = $multiplication;
    return $totalScore;
  }
}