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
} else {
  $text = preg_replace('/[^\wÖÄÜß]/', '', strtoupper($text));
  $characters = str_split($text);

  $i = 0;

  $converted = '';

  while ($i < count($characters)) {
    if (is_int($characters[$i]) && $characters[$i] != 0) {
      //Figure
      $zeros = 0;
      for ($x = 1; $x < 3; $x++) {
        if ($characters[$i + $x] == 0) {
          $zeros += 1;
        }
      }
      if ($zeros == 2) {
        $converted .= $alphabet[$characters[$i]] . ' HUNDRED';
        $i += 2;
      } else if ($zeros == 3) {
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
}