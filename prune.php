<?php

use DBA\AnswerSession;

require_once(dirname(__FILE__) . "/inc/load.php");
if (!$LOGIN->isLoggedin()) {
  header("Location: admin.php?err=4" . time());
  die();
}
$OBJECTS['pageTitle'] = "Cineast Evaluator";

$lastId = 0;
$queryId = 0;
if (isset($_GET['queryId'])) {
  $query = $FACTORIES::getQueryFactory()->get($_GET['queryId']);
  if ($query != null) {
    $queryId = $query->getId();
    $_SESSION['queryId'] = $query->getId();
    $_SESSION['lastId'] = 0;
    $pruneSession = new AnswerSession(0, null, $LOGIN->getUserID(), null, 1, 1, time(), Util::getIP(), Util::getUserAgentHeader());
    $pruneSession = $FACTORIES::getAnswerSessionFactory()->save($pruneSession);
    $_SESSION['pruneSessionId'] = $pruneSession->getId();
    header("Location: prune.php");
  }
}
else {
  if (isset($_SESSION['queryId'])) {
    $queryId = $_SESSION['queryId'];
  }
  if (isset($_SESSION['lastId'])) {
    $lastId = $_SESSION['lastId'];
  }
}

if (isset($_POST['answer'])) {
  $pruneHandler = new PruneHandler();
  $pruneHandler->handle($_POST['answer']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

$question = Util::getNextPruneQuestion($queryId, $lastId);

if ($question == null) {
  // TODO: make fancy forward here
  die("You went trough all answers!");
}

Util::prepare2CompareQuestion($question->getMediaObjects()[0], $question->getMediaObjects()[1], true);
$OBJECTS['tuple'] = $question->getResultTuples()[0];

$debug = array(
  "Last ID: " . $_SESSION['lastId'],
  "Prune Session ID: " . $_SESSION['pruneSessionId'],
  "Query ID: " . $_SESSION['queryId']
);
if ($queryId > 0) {
  $debug[] = "ResultTuples left to prune: " . Util::getPruneLeft($queryId, $lastId);
}
$OBJECTS['debug'] = $debug;

$TEMPLATE = new Template("views/prune");
echo $TEMPLATE->render($OBJECTS);