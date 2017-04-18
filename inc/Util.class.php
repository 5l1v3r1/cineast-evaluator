<?php
use DBA\JoinFilter;
use DBA\MediaObject;
use DBA\MediaType;
use DBA\OrderFilter;
use DBA\Query;
use DBA\QueryFilter;
use DBA\QueryResultTuple;
use DBA\RandOrderFilter;
use DBA\ResultTuple;

/**
 *
 * @author Sein
 *
 *         Bunch of useful static functions.
 */
class Util {
  /**
   * Calculates variable. Used in Templates.
   * @param $in mixed calculation to be done
   * @return mixed
   */
  public static function calculate($in) {
    return $in;
  }
  
  public static function checkFolders() {
    $tempDir = STORAGE_PATH . TMP_FOLDER;
    if (!file_exists($tempDir)) {
      mkdir($tempDir);
    }
    $queryDir = STORAGE_PATH . QUERIES_FOLDER;
    if (!file_exists($queryDir)) {
      mkdir($queryDir);
    }
    $mediaDir = STORAGE_PATH . MEDIA_FOLDER;
    if (!file_exists($mediaDir)) {
      mkdir($mediaDir);
    }
  }
  
  /**
   * @param $resultTuples ResultTuple[]
   * @param $queryResultTuples QueryResultTuple[]
   * @param $excludingTuples int[]
   * @return ResultTuple
   */
  public static function getTupleWeightedWithRankAndSigma($resultTuples, $queryResultTuples, $excludingTuples) {
    if (sizeof($resultTuples) == 0) {
      return null;
    }
    
    $highestRank = 0;
    foreach ($queryResultTuples as $queryResultTuple) {
      if ($queryResultTuple->getRank() > $highestRank) {
        $highestRank = $queryResultTuple->getRank();
      }
    }
    
    $totalCount = 0;
    for ($i = 0; $i < sizeof($resultTuples); $i++) {
      if (in_array($resultTuples[$i]->getId(), $excludingTuples)) {
        continue; // exclude the already answered tuples
      }
      $add = 1;
      if ($resultTuples[$i]->getSigma() == -1) {
        $add += 2; // TODO: elaborate this value, or make it dependant from highest rank
      }
      $totalCount += $add + sqrt($highestRank - $queryResultTuples[$i]->getRank());
    }
    
    $random = random_int(0, $totalCount - 1);
    $currentCount = 0;
    for ($i = 0; $i < sizeof($resultTuples); $i++) {
      if (in_array($resultTuples[$i]->getId(), $excludingTuples)) {
        continue; // exclude the already answered tuples
      }
      $add = 1;
      if ($resultTuples[$i]->getSigma() == -1) {
        $add += 2; // TODO: elaborate this value, or make it dependant from highest rank
      }
      $currentCount += $add + sqrt($highestRank - $queryResultTuples[$i]->getRank());
      if ($currentCount > $random) {
        return $resultTuples[$i];
      }
    }
    return null;
  }
  
  /**
   * @param $mediaObject1 MediaObject
   * @param $mediaObject2 MediaObject
   * @param $randomOrder bool
   */
  public static function prepare2CompareQuestion($mediaObject1, $mediaObject2, $randomOrder = true) {
    global $FACTORIES, $OBJECTS;
    
    $value1 = new DataSet();
    $value2 = new DataSet();
    
    if (random_int(0, 1) > 0 && $randomOrder) {
      $m = $mediaObject2;
      $mediaObject2 = $mediaObject1;
      $mediaObject1 = $m;
    }
    
    $value1->addValue('objData', array("serve.php?id=" . $mediaObject1->getChecksum()));
    $value2->addValue('objData', array("serve.php?id=" . $mediaObject2->getChecksum()));
    
    $mediaType1 = $FACTORIES::getMediaTypeFactory()->get($mediaObject1->getMediaTypeId());
    $mediaType2 = $FACTORIES::getMediaTypeFactory()->get($mediaObject2->getMediaTypeId());
    
    $value1->addValue('template', $mediaType1->getTemplate());
    $value2->addValue('template', $mediaType2->getTemplate());
    
    $OBJECTS['object1'] = $mediaObject1;
    $OBJECTS['object2'] = $mediaObject2;
    $OBJECTS['value1'] = $value1;
    $OBJECTS['value2'] = $value2;
  }
  
  /**
   * @param $queries Query[]
   * @return Query
   */
  public static function getQueryWeightedWithPriority($queries) {
    if (sizeof($queries) == 0) {
      return null;
    }
    
    $totalPriority = 0;
    foreach ($queries as $query) {
      $totalPriority += $query->getPriority() + 1;
    }
    
    $random = random_int(0, $totalPriority - 1);
    $currentPriority = 0;
    foreach ($queries as $query) {
      $currentPriority += $query->getPriority() + 1;
      if ($currentPriority > $random) {
        return $query;
      }
    }
    return $queries[sizeof($queries) - 1];
  }
  
  /**
   * Converts a given string to hex code.
   *
   * @param string $string
   *          string to convert
   * @return string converted string into hex
   */
  public static function strToHex($string) {
    return implode(unpack("H*", $string));
  }
  
  public static function getExtension($file) {
    $basename = explode(".", basename($file));
    return strtolower($basename[sizeof($basename) - 1]);
  }
  
  /**
   * @param $object1 MediaObject
   * @param $object2 MediaObject
   */
  public static function getResultTuple($object1, $object2) {
    global $FACTORIES;
    
    $qF1 = new QueryFilter(ResultTuple::OBJECT_ID1, $object1->getId(), "=");
    $qF2 = new QueryFilter(ResultTuple::OBJECT_ID2, $object2->getId(), "=");
    return $FACTORIES::getResultTupleFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2)), true);
  }
  
  public static function getMediaObject($checksum) {
    global $FACTORIES;
    
    $qF = new QueryFilter(MediaObject::CHECKSUM, $checksum, "=");
    return $FACTORIES::getMediaObjectFactory()->filter(array($FACTORIES::FILTER => $qF), true);
  }
  
  public static function getMediaType($file) {
    global $FACTORIES;
    
    $extension = Util::getExtension($file);
    
    $qF = new QueryFilter(MediaType::EXTENSION, $extension, "=");
    $mediaType = $FACTORIES::getMediaTypeFactory()->filter(array($FACTORIES::FILTER => $qF), true);
    if ($mediaType == null) {
      // create this new media type
      $mediaType = new MediaType(0, $extension, $extension, null);
      $mediaType = $FACTORIES::getMediaTypeFactory()->save($mediaType);
    }
    return $mediaType;
  }
  
  /**
   * @param $mediaObjectId int
   * @return string
   */
  public static function getMediaTypeNameForObject($mediaObjectId) {
    global $FACTORIES;
    
    $qF = new QueryFilter(MediaObject::MEDIA_OBJECT_ID, $mediaObjectId, "=");
    $jF = new JoinFilter($FACTORIES::getMediaTypeFactory(), MediaObject::MEDIA_TYPE_ID, MediaType::MEDIA_TYPE_ID);
    $joined = $FACTORIES::getMediaObjectFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    if (sizeof($joined['MediaType']) == 0) {
      return "unknown";
    }
    /** @var MediaType $mediaType */
    $mediaType = $joined['MediaType'][0];
    return $mediaType->getTypeName();
  }
  
  /**
   * This filesize is able to determine the file size of a given file, also if it's bigger than 4GB which causes
   * some problems with the built-in filesize() function of PHP.
   * @param $file string Filepath you want to get the size from
   * @return int -1 if the file doesn't exist, else filesize
   */
  public static function filesize($file) {
    if (!file_exists($file)) {
      return -1;
    }
    $fp = fopen($file, "rb");
    $pos = 0;
    $size = 1073741824;
    fseek($fp, 0, SEEK_SET);
    while ($size > 1) {
      fseek($fp, $size, SEEK_CUR);
      
      if (fgetc($fp) === false) {
        fseek($fp, -$size, SEEK_CUR);
        $size = (int)($size / 2);
      }
      else {
        fseek($fp, -1, SEEK_CUR);
        $pos += $size;
      }
    }
    
    while (fgetc($fp) !== false) {
      $pos++;
    }
    
    return $pos;
  }
  
  public static function getSecurityQuestion() {
    global $FACTORIES;
    
    $question = null;
    
    $qF1 = new QueryFilter(ResultTuple::SIGMA, SECURITY_QUESTION_THRESHOLD, "<=");
    $qF2 = new QueryFilter(ResultTuple::SIGMA, 0, ">=");
    $oF = new RandOrderFilter(10);
    $resultTuples = $FACTORIES::getResultTupleFactory()->filter(array($FACTORIES::FILTER => array($qF1, $qF2), $FACTORIES::ORDER => $oF));
    $questionType = SessionQuestion::TYPE_UNDEFINED;
    if (sizeof($resultTuples) >= 2 && mt_rand(0, 1) > 0) {
      $matching = array();
      for ($i = 0; $i < sizeof($resultTuples); $i++) {
        for ($j = $i + 1; $j < sizeof($resultTuples); $j++) {
          if ($resultTuples[$i]->getObjectId1() == $resultTuples[$j]->getObjectId1() && $resultTuples[$i]->getObjectId1() != $resultTuples[$j]->getObjectId1()) {
            $matching = array($resultTuples[$i], $resultTuples[$j]);
            $i = sizeof($resultTuples);
            break;
          }
        }
      }
      if (sizeof($matching) > 0) {
        // three compare
        $questionType = SessionQuestion::TYPE_COMPARE_TRIPLE;
        $mediaObject1 = $FACTORIES::getMediaObjectFactory()->get($matching[0]->getObjectId1());
        $mediaObject2 = $FACTORIES::getMediaObjectFactory()->get($matching[0]->getObjectId2());
        $mediaObject3 = $FACTORIES::getMediaObjectFactory()->get($matching[1]->getObjectId2());
        $question = new SessionQuestion($questionType, array($mediaObject1, $mediaObject2, $mediaObject3), $matching);
      }
    }
    
    
    if (sizeof($resultTuples) > 0 && $questionType == SessionQuestion::TYPE_UNDEFINED) {
      // two compare
      $questionType = SessionQuestion::TYPE_COMPARE_TWO;
      $mediaObject1 = $FACTORIES::getMediaObjectFactory()->get($resultTuples[0]->getObjectId1());
      $mediaObject2 = $FACTORIES::getMediaObjectFactory()->get($resultTuples[0]->getObjectId2());
      $question = new SessionQuestion($questionType, array($mediaObject1, $mediaObject2), array($resultTuples[0]));
    }
    
    return $question;
  }
  
  public static function resizeImage($path) {
    $size = getimagesize($path);
    if ($size[0] <= IMAGE_MAX_WIDTH && $size[1] <= IMAGE_MAX_HEIGHT) {
      return; // we don't need to do a resize here
    }
    $ratio = $size[0] / $size[1]; // width/height
    if ($ratio > 1) {
      $width = IMAGE_MAX_WIDTH;
      $height = IMAGE_MAX_HEIGHT / $ratio;
    }
    else {
      $width = IMAGE_MAX_WIDTH * $ratio;
      $height = IMAGE_MAX_HEIGHT;
    }
    $src = imagecreatefromstring(file_get_contents($path));
    $dst = imagecreatetruecolor($width, $height);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
    imagedestroy($src);
    imagejpeg($dst, $path);
    imagedestroy($dst);
  }
  
  /**
   * Tries to determine the IP of the client.
   * @return string 0.0.0.0 or the client IP
   */
  public static function getIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    if (!$ip) {
      return "0.0.0.0";
    }
    return $ip;
  }
  
  /**
   * Returns the username from the given userId
   * @param $id int ID for the user
   * @return string username or unknown-id
   */
  public static function getUsernameById($id) {
    global $FACTORIES;
    
    $user = $FACTORIES::getUserFactory()->get($id);
    if ($user === null) {
      return "Unknown-$id";
    }
    return $user->getUsername();
  }
  
  public static function getUserAgentHeader() {
    return json_encode(getallheaders());
  }
  
  /**
   * Cut a string to a certain number of letters. If the string is too long, instead replaces the last three letters with ...
   * @param $string String you want to short
   * @param $length Number of Elements you want the string to have
   * @return string Formatted string
   */
  public static function shortenstring($string, $length) {
    // shorten string that would be too long
    $return = "<span title='$string'>";
    if (strlen($string) > $length) {
      $return .= substr($string, 0, $length - 3) . "...";
    }
    else {
      $return .= $string;
    }
    $return .= "</span>";
    return $return;
  }
  
  /**
   * This sends a given email with text and subject to the address.
   *
   * @param string $address
   *          email address of the receiver
   * @param string $subject
   *          subject of the email
   * @param string $text
   *          html content of the email
   * @return true on success, false on failure
   */
  public static function sendMail($address, $subject, $text) {
    //TODO: make sending email configurable
    
    $header = "Content-type: text/html; charset=utf8\r\n";
    $header .= "From: todo <todo@to.do>\r\n";
    if (!mail($address, $subject, $text, $header)) {
      return false;
    }
    return true;
  }
  
  /**
   * Generates a random string with mixedalphanumeric chars
   *
   * @param int $length
   *          length of random string to generate
   * @return string random string
   */
  public static function randomString($length) {
    $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $result = "";
    for ($x = 0; $x < $length; $x++) {
      $result .= $charset[mt_rand(0, strlen($charset) - 1)];
    }
    return $result;
  }
  
  /**
   * @return SessionQuestion
   */
  public static function getNextPruneQuestion($queryId = 0, $lastId = 0) {
    global $FACTORIES;
    
    $qF = new QueryFilter(ResultTuple::IS_FINAL, "0", "=");
    $oF = new OrderFilter(ResultTuple::RESULT_TUPLE_ID, "ASC LIMIT 1");
    $options = array($FACTORIES::FILTER => $qF, $FACTORIES::ORDER => $oF);
    if ($queryId > 0) {
      $filters[$FACTORIES::JOIN] = new JoinFilter($FACTORIES::getQueryResultTupleFactory(), ResultTuple::RESULT_TUPLE_ID, QueryResultTuple::RESULT_TUPLE_ID);
      $filters[$FACTORIES::FILTER][] = new QueryFilter(QueryResultTuple::QUERY_ID, $queryId, "=", $FACTORIES::getQueryResultTupleFactory());
    }
    else if($lastId > 0){
      $filters[$FACTORIES::FILTER][] = new QueryFilter(ResultTuple::RESULT_TUPLE_ID, $lastId, ">");
    }
    $resultTuple = $FACTORIES::getResultTupleFactory()->filter($options, true);
    if($resultTuple == null){
      return null;
    }
    return new SessionQuestion(
      SessionQuestion::TYPE_COMPARE_TWO,
      array($FACTORIES::getMediaObjectFactory()->get($resultTuple->getObjectId1()), $FACTORIES::getMediaObjectFactory()->get($resultTuple->getObjectId2())),
      array($resultTuple)
    );
  }
}









