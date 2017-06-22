<?php
function pwCheck($pass) {
  $re    = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&\'])[^ ]{8,}$/';
  $match = preg_match($re, $pass);

  if ($match) {
    $array = str_split($pass);

    foreach ($array as $item) {
      $result = substr_count($pass, $item);

      if ($result > 2) {
        return FALSE;
      }
    }
    return TRUE;
  }

  return FALSE;
}
?>
