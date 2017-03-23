<?php
/**
 * Created by IntelliJ IDEA.
 * User: sein
 * Date: 22.03.17
 * Time: 17:35
 */

/** @var $OBJECTS array */

require_once(dirname(__FILE__) . "/inc/load.php");
$TEMPLATE = new Template("content/home");

$MENU->setActive("home");
$OBJECTS['pageTitle'] = "Cineast Evaluator";

$error = false;
if (isset($_GET['err'])) {
  $errNum = substr($_GET['err'], 0, 1);
  $errTime = substr($_GET['err'], 1);
  if (time() - $errTime < 60) {
    switch ($errNum) {
      case 1:
        $errorMessage = "You need to fill in all fields!";
        break;
      case 2:
        $errorMessage = "Fields cannot be empty!";
        break;
      case 3:
        $errorMessage = "Invalid username/password!";
        break;
      case 4:
        $errorMessage = "You were logged out due to inactivity!";
        break;
      default:
        $errorMessage = "An unknown error happened!";
        break;
    }
    $error = true;
    $OBJECTS['errorMessage'] = $errorMessage;
  }
}
$OBJECTS['error'] = $error;

$success = false;
if (isset($_GET['logout'])) {
  $success = true;
  $successMessage = "You logged out successfully!";
  $OBJECTS['successMessage'] = $successMessage;
}
$OBJECTS['success'] = $success;

echo $TEMPLATE->render($OBJECTS);