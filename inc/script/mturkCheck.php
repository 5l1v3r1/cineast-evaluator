<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 19.05.17
 * Time: 11:04
 */

require_once(dirname(__FILE__) . "/../load.php");

// conntect to API
$credentials = json_decode(file_get_contents(dirname(__FILE__) . "/../mturk_api_key.json"), true);
$client = new \Aws\MTurk\MTurkClient(array(
    "version" => "latest",
    "region" => "us-east-1",
    "endpoint" => "https://mturk-requester-sandbox.us-east-1.amazonaws.com",
    "credentials" => $credentials
  )
);

$result = $client->listHITs();
$hits = $result->toArray()['HITs'];
foreach ($hits as $hit) {
  print_r($hit);
  die();
  $assignments = $client->listAssignmentsForHIT(array(
      "HITId" => $hit['HITId'],
      "AssignmentStatuses" => array("Submitted")
    )
  );
}

print_r($hits);

