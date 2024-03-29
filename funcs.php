<?php
function makeApiRequest($method, $data) {
  global $config, $client;
  if (!($client instanceof \GuzzleHttp\Client)) {
    $client = new \GuzzleHttp\Client(['base_uri' => $config['url']]);
  }
  try {
    $response = $client->request('POST', $method, array('json' => $data));
  } catch (\GuzzleHttp\Exception\BadResponseException $e) {
    $body = $e->getResponse()->getBody();
    mail($config['mail'], 'Error', print_r($body->getContents(), true) . "\n" . print_r($data, true) . "\n" . __FILE__);
    return false;
  }
  return json_decode($response->getBody(), true)['result'];
}

function sendMessage($chatId, $text, $replyTo = '', $replyMarkup = '') {
  if (mb_strlen($text) > 4096){
    sendMessage($chatId, substr($text, 0, 4096), $replyTo, $replyMarkup);
    return sendMessage($chatId, substr($text, 4096), $replyTo, $replyMarkup);
  }
  else {
    $data = array(
      'disable_web_page_preview' => true,
      'parse_mode' => 'html',
      'chat_id' => $chatId,
      'text' => $text,
      'reply_to_message_id' => $replyTo,
      'reply_markup' => $replyMarkup
    );
    return makeApiRequest('sendMessage', $data);
  }
}

function logMessage($userId, $chatId, $original, $converted){
  file_put_contents('usageLog.txt', $userId . '|' . $chatId . '|' . $original . '|' . $converted . "\n", FILE_APPEND);
}