<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 27.03.17
 * Time: 18:24
 */

require_once(dirname(__FILE__) . "/../load.php");

// TODO: get which script should be used
// TODO: include the selected similarity calculator

require_once(dirname(__FILE__)."/similarities/Calculator.class.php");
require_once(dirname(__FILE__)."/similarities/ExampleCalculator.class.php");
require_once(dirname(__FILE__)."/similarities/SimilaritySumCalculator.class.php");

echo "calculating...\n";

$calculator = new SimilaritySumCalculator();
$resultSets = $FACTORIES::getResultTupleFactory()->filter(array());
$changed = array();
$calculator->updateSimilarities($resultSets, $changed);

echo "updating...\n";

foreach($resultSets as $resultSet){
  if(isset($changed[$resultSet->getId()])) {
    $FACTORIES::getResultTupleFactory()->update($resultSet);
  }
}

echo "finished!\n";