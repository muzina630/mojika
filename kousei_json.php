<?php

$yhost = "https://jlp.yahooapis.jp/KouseiService/V1/kousei";
$appid ="dj00aiZpPWZMYUJJU3pwc1RxcSZzPWNvbnN1bWVyc2VjcmV0Jng9ZTg-";

$keyword = [
"StartPos",
"Length",
"Surface",
"ShitekiWord",
"ShitekiInfo"
];

$vals = array();
$datas = array();


if( isset($_GET["sentence"])) {
  $sentence = $_GET["sentence"];
} else {
  $sentence = "これは文章校正のテストです。この文は更正されてますか？遙か彼方に>小形飛行機が見える。";
}

$url = $yhost . "?appid=" . $appid . "&sentence=" . urlencode($sentence);

//$context = stream_context_create(array(
//      'http' => array('ignore_errors' => true)
// ));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$xml = curl_exec($ch);
curl_close($ch);

// XML -> json
$pos1=0;
$pos2=0;
$opos=0;
$i = 0;
$limit = mb_strlen($xml);
$sw=0;
while( $i < $limit ) {
  for($j=0; $j < count($keyword); $j++) {
    $pos1 = SerchTag( $xml, $keyword[$j], $opos ) + mb_strlen( $keyword[$j] ) +1;
    $tmps = "/" . $keyword[$j];
    $pos2 = SerchTag( $xml, $tmps, $pos1 ) -1;
    if( $pos1 < $limit ) {
      $vals[$j] = mb_substr( $xml, $pos1, $pos2 - $pos1 );
      $opos = $pos2 + strlen($tmps);
    }
    $opos = $pos2 + strlen($tmps);
    if( $pos1 > $limit ) {
      $sw=1;
      break;
    }
  }
  if( $sw == 0 ) {
    array_push( $datas, $vals );
  }
  $i=$opos;
}

$json =  "{\n";
$json = $json . "  \"kousei\": [\n";
for($i=0; $i<count($datas); $i++) {
  $json = $json . "    { ";
  $vals = $datas[$i];
  for($j=0; $j<count($vals); $j++) {
    $tmps =  "\"" . $keyword[$j] . "\": \"" . $vals[$j]. "\"";
    if( $j < count($vals)-1 ) {
      $tmps = $tmps . ",";
    }
    $json = $json . $tmps;
  }
  if( $i < count($datas) -1 ) {
    $json = $json . " },\n";
  } else {
    $json = $json . " }\n";
  }
}
$json = $json . "  ]\n";
$json = $json . "}\n";


// $body = file_get_contents($url, false, $context);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
print $json;
//print "\n";
//print $url;
//print "\n";

function SerchTag( $xml, $key, $offset ) {
  $max = mb_strlen($xml);
  if($max > $offset ) {
    $pos = mb_strpos( $xml, $key, $offset );
    if( $pos == false ) {
      $pos = $max;
    }
  } else {
    $pos = $max;
  }
  return($pos);
}

?>

