<?php

require_once(dirname(__FILE__) . "/../load.php");

/** @var $VALIDATORS Validator[] */

/*
 * IMPORTANT:
 * This script is only for experimental purposes and should not be called regularly
 */

$session = $FACTORIES::getAnswerSessionFactory()->get($argv[1]);
$validator = new MultivariantCrowdValidator();
$currentValidity = $validator->validateFinished($session, 0, true);
$validator = new PatternValidator();
echo $currentValidity . "\n";
$currentValidity = $validator->validateFinished($session, $currentValidity);
$validator = new TimeValidator();
echo $currentValidity . "\n";
$currentValidity = $validator->validateFinished($session, $currentValidity);
echo $currentValidity . "\n";

$calculator = new ScoreCalculator($session, true);
echo "Base: " . $calculator->getScore()[ScoreCalculator::SCORE_BASE] . "\n";
  




