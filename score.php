<?php

/** @var $OBJECTS array */

use DBA\Game;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/inc/load.php");
$TEMPLATE = new Template("content/home");

$MENU->setActive("score");
$OBJECTS['pageTitle'] = "Game Score";
$TEMPLATE = new Template("content/score");

$answerSession = null;
$isFresh = false;
if (isset($_GET['game'])) {
  // we can show historical scores
}
else {
  $answerSession = $FACTORIES::getAnswerSessionFactory()->get($_SESSION['answerSessionId']);
  if ($answerSession->getIsOpen() != 0 || $answerSession->getUserId() != null || $answerSession->getMicroworkerId() != null || ($OAUTH->isLoggedin() && $answerSession->getPlayerId() != $OAUTH->getPlayer()->getId())) {
    $answerSession = null;
  }
  else {
    $isFresh = true;
  }
}

if ($answerSession == null) {
  header("Location: index.php"); // TODO: maybe show some message here
  die();
}

// we show a score here
$scoreCalculator = new ScoreCalculator($answerSession);
$scoreData = $scoreCalculator->getScore();
$OBJECTS['score'] = new DataSet($scoreData);

$OBJECTS['achievements'] = array();
if ($isFresh) {
  // test if game was saved for this answer session
  if ($OAUTH->isLoggedin()) {
    $qF = new QueryFilter(Game::ANSWER_SESSION_ID, $answerSession->getId(), "=");
    $game = $FACTORIES::getGameFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($game == null) {
      $game = new Game(0, $OAUTH->getPlayer()->getId(), $answerSession->getId(), time(), $scoreData[ScoreCalculator::SCORE_BASE], $scoreData[ScoreCalculator::SCORE_TOTAL]);
      $FACTORIES::getGameFactory()->save($game);
    }
  }
  
  // TODO: test achievements and add it as info to page
  $achievementTester = new AchievementTester();
  $OBJECTS['achievements'] = $achievementTester->getAchievements($OAUTH->getPlayer());
}

echo $TEMPLATE->render($OBJECTS);