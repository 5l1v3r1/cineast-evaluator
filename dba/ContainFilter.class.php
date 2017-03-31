<?php

namespace DBA;

class ContainFilter extends Filter {
  private $key;
  private $values;
  private $negative;
  
  function __construct($key, $values, $negative = false) {
    $this->key = $key;
    $this->values = $values;
    $this->negative = $negative;
  }
  
  function getQueryString($table = "") {
    if ($table != "") {
      $table = $table . ".";
    }
    $app = array();
    for ($x = 0; $x < sizeof($this->values); $x++) {
      $app[] = "?";
    }
    
    $prep = "";
    if ($this->negative) {
      $prep = " NOT ";
    }
    
    return $table . $this->key . $prep . " IN (" . implode(",", $app) . ")";
  }
  
  function getValue() {
    return $this->values;
  }
  
  function getHasValue() {
    return true;
  }
}
