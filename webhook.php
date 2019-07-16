<?php
require_once(__DIR__ . "/funcs.php");
require_once(__DIR__ . "/config.php");
require_once('/var/libraries/composer/vendor/autoload.php');
//^ guzzlehttp

$response = file_get_contents('php://input');
$data = json_decode($response, true);
$dump = print_r($data, true);

$chatId = $data['message']['chat']['id'];
$chatType = $data['message']['chat']['type'];
$senderUserId = preg_replace("/[^0-9]/", "", $data['message']['from']['id']);

if (isset($data['message']['text'])) {
  $text = $data['message']['text'];
}

if ($text == '/start') {
  sendMessage($chatId, 'Send me text and I will convert it into the phonetic alphabet.');
} else {
  $converted = '';
  //Test if phonetic
  $phonetics = explode(' ', preg_replace('/[^A-Z ]/', '', strtoupper($text)));
  if (!empty($phonetics)) {
    $isPhonetic = true;
    $flippedAlphabet = array_change_key_case(array_flip($alphabet), CASE_UPPER);

    foreach ($phonetics as $phonetic) {
      if (!isset($flippedAlphabet[$phonetic]) && $phonetic !== ' ') {
        $isPhonetic = false;
        $converted = '';
        break;
      } else {
        $converted .= $flippedAlphabet[$phonetic];
      }
    }
  } else {
    $isPhonetic = false;
  }

  if ($isPhonetic) {
    sendMessage($chatId, $converted);
    logMessage($senderUserId, $chatId, $text, $converted);
  } else {
    $text = str_replace(['ö', 'Ö'], 'OE', str_replace(['ä', 'Ä'], 'AE', str_replace(['ü', 'Ü'], 'UE', $text)));
    $text = preg_replace('/[^\w.ß\- ]/', '', strtoupper($text));
    if (empty($text)) {
      die();
    }
    $characters = str_split($text);

    $i = 0;

    while ($i < count($characters)) {
      if (is_numeric($characters[$i]) && $characters[$i] != 0) {
        //Figure
        $zeros = 0;
        for ($x = 1; $x <= 3; $x++) {
          if ($characters[$i + $x] === '0') {
            $zeros += 1;
          } else {
            $x = 4;
          }
        }
        if ($zeros < 2) {
          $converted .= $alphabet[$characters[$i]];
        } else if ($zeros === 2) {
          $converted .= $alphabet[$characters[$i]] . ' HUNDRED';
          $i += 2;
        } else if ($zeros === 3) {
          $converted .= $alphabet[$characters[$i]] . ' THOUSAND';
          $i += 3;
        }
      } else {
        //Character
        $converted .= $alphabet[$characters[$i]];
      }

      $i += 1;
      $converted .= ' ';
    }
    sendMessage($chatId, $converted);
    logMessage($senderUserId, $chatId, $text, $converted);
  }
}