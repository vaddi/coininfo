<?php

//
// A PHP Luno aka bitx API 2 Prometheus Endpoint
//

$configfile = __DIR__ . '/config.php';
if( ! is_file( $configfile ) ) {
  echo "Unable to load config file.";
  exit;
} else {
  require_once( $configfile );
}

define( 'APPNAME', 'Coin Info Exporter' );
define( 'VERSION', '1.7' );

// target definition (xbteur and balance are neccessary, all other are optionals)
$otherTargets = array(
  'xbteur' => array(
    'desc' => 'bitx ticker data, bitx courrent BTC course',
    'data' => getData( APIURL . 'ticker?pair=XBTEUR' )
  ),
  'etheur' => array(
    'desc' => 'bitx ticker data, bitx courrent ETH course',
    'data' => getData( APIURL . 'ticker?pair=ETHEUR' )
  ),
  'bitaps' => array(
    'desc' => 'Current Bitcoin course by bitaps.com',
    'data' => getData( 'https://api.bitaps.com/market/v1//ticker/btceur' )->data->last
  ),
  'coindesk' => array(
    'desc' => 'Current Bitcoin course by coindesk.com',
    'data' => getData( 'https://api.coindesk.com/v1/bpi/currentprice.json' )->bpi->EUR->rate_float
  ),
  'blockchain' => array(
    'desc' => 'Current Bitcoin course by blockchain.info',
    'data' => getData( 'https://blockchain.info/ticker' )->EUR->last
  ),
  // 'name' => array(
  //   'desc' => 'Some descr of the Metric and where there are from',
  //   'data' => getData( 'url' )
  // ),
);

// The Exporter Error Codes 
// 
// 0    = No Error
// 1..X = Simple numberings of erros in wallet processing, one id for each error
// 401  = No API Key set
// 403  = No API Secret set
// 500  = Error on balance API call

if( ! extension_loaded('curl') ) {
  echo 'Please install php_curl to use this Application';
  exit;
}

// funktion to get data from url as array (json response 2 php array)
function getData( $query = null ) {
  $cURLConnection = curl_init();
  curl_setopt($cURLConnection, CURLOPT_URL, $query );
  curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
  if( strpos( $query, 'api.mybitx.com' ) !== FALSE ) {
    // only use apikey only on bitx api calls
    curl_setopt($cURLConnection, CURLOPT_USERPWD, APIKEY.":".APISEC );
  }
  $data = curl_exec($cURLConnection);
  curl_close($cURLConnection);
  $data = json_decode( $data );
  return $data;
}

// create the real targets
// bitx API depends on have we both, Key & Secret
if( APIKEY != "" && APISEC != "" ) {
  $targets = array(
    'balance' =>  array(
      'desc' => 'bitx API Addresse to wallets',
      'data' => getData( APIURL . 'balance' )
    ),
  );
  // add all other
  foreach( $otherTargets as $name => $target ) {
    $targets[ $name ] = $target;
  }
} else {
  // just set other as default targets
  $targets = $otherTargets;
}

//
// Start Output
//

// Output for Prometheus (simple plain text)
header("Content-type: text/plain; charset=utf-8");
http_response_code( 200 );

echo "# HELP ci_info " . APPNAME . " Info Metric with constant value 1\n";
echo "# TYPE ci_info gauge\n";
echo "ci_info{version=\"" . VERSION . "\",nodename=\"" . $_SERVER['HTTP_HOST'] . "\"} 1\n";

// some static bitx metrics
if( isset( $targets['xbteur'] ) && isset( $targets['xbteur']['data'] ) && $targets['xbteur']['data'] != null && $targets['xbteur']['data'] != "" ) {
  echo "# HELP ci_xbteur_bid Current Luno Bitcoin bid in Euro\n";
  echo "# TYPE ci_xbteur_bid gauge\n";
  echo "ci_xbteur_bid " . $targets['xbteur']['data']->bid . "\n";

  echo "# HELP ci_xbteur_ask Current Luno Bitcoin ask in Euro\n";
  echo "# TYPE ci_xbteur_ask gauge\n";
  echo "ci_xbteur_ask " . $targets['xbteur']['data']->ask . "\n";

  echo "# HELP ci_xbteur_last_trade Luno Last Bitcoin Trade in Euro\n";
  echo "# TYPE ci_xbteur_last_trade gauge\n";
  echo "ci_xbteur_last_trade " . $targets['xbteur']['data']->last_trade . "\n";

  echo "# HELP ci_xbteur_rolling_24 Luno Rolling 24 h Bitcoin Volume in Euro\n";
  echo "# TYPE ci_xbteur_rolling_24 gauge\n";
  echo "ci_xbteur_rolling_24 " . $targets['xbteur']['data']->rolling_24_hour_volume . "\n";
}

if( isset( $targets['etheur'] ) && isset( $targets['etheur']['data'] ) && $targets['etheur']['data'] != null && $targets['etheur']['data'] != "" ) {
  echo "# HELP ci_etheur_bid Current Luno Ethereum bid in Euro\n";
  echo "# TYPE ci_etheur_bid gauge\n";
  echo "ci_etheur_bid " . $targets['etheur']['data']->bid . "\n";
  
  echo "# HELP ci_etheur_ask Current Luno Ethereum ask in Euro\n";
  echo "# TYPE ci_etheur_ask gauge\n";
  echo "ci_etheur_ask " . $targets['etheur']['data']->ask . "\n";

  echo "# HELP ci_etheur_last_trade Luno Last Ethereum Trade in Euro\n";
  echo "# TYPE ci_etheur_last_trade gauge\n";
  echo "ci_etheur_last_trade " . $targets['etheur']['data']->last_trade . "\n";

  echo "# HELP ci_etheur_rolling_24 Luno Rolling 24 h Ethereum Volume in Euro\n";
  echo "# TYPE ci_etheur_rolling_24 gauge\n";
  echo "ci_etheur_rolling_24 " . $targets['etheur']['data']->rolling_24_hour_volume . "\n";
}

// output all other targets
foreach( $targets as $name => $target ) {
  if( $name != 'xbteur' && $name != 'etheur' && $name != 'balance' ) { // excluding xbteur and balance
    echo "# HELP ci_" . $name . "_last " . $target['desc'] . "\n";
    echo "# TYPE ci_" . $name . "_last gauge\n";
    echo "ci_" . $name . "_last " . $target['data'] . "\n";
  }
}

// bitx balance data
if( isset( $targets['balance'] ) && $targets['balance'] != null && $targets['balance'] != "" ) {
  // static field values, each balance/wallet has this three
  $fields = array( 'balance', 'reserved', 'unconfirmed' );
  $err = null;
  echo "# HELP ci_balance Luno wallet data, each has balance, reserved and unconfirmed field\n";
  echo "# TYPE ci_balance gauge\n";
  foreach( $targets['balance']['data']->balance as $currency => $values ) {
    foreach( $fields as $fieldid => $fieldvalue ) {
      if( isset( $values->$fieldvalue ) && $values->$fieldvalue != null && $values->$fieldvalue != "" ) {
        echo "ci_balance{asset=\"" . $values->asset  . "\",field=\"" . $fieldvalue . "\"} " . $values->$fieldvalue . "\n";
      } else {
        $err[] = $fieldvalue . " is empty";
      }
    }
  }
}

// output error metric header
echo "# HELP ci_error " . APPNAME . " Error Metric, helpfull Informations what might be wrong, should be 0 = No Errors\n";
echo "# TYPE ci_error gauge\n";
if( APIKEY == "" && APISEC == "" ) {
  // none of Luno data was set, so we dont want to show the API Errors
  if( isset( $err ) && $err != null && is_array( $err ) ) {
    // show exporter errors
    foreach( $err as $id => $errmsg ) {
      echo "ci_error{errorid=\"" . $id . "\",error=\"" . $errmsg . "\"} 1\n";
    }
  } else {
    // default, no errors
    echo "ci_error{errorid=\"0\",error=\"No Errors\"} 0\n";
  }
} else {
  // api call errors should be showened, we have both
  if( isset( $targets['balance']['data']->error ) ) {
    // show bitx balacne errors
    echo "ci_error{errorid=\"500\",error=\"" . $targets['balance']['data']->error . "\"} 1\n";
  } else if( isset( $err ) && $err != null && is_array( $err ) ) {
    // show exporter errors
    foreach( $err as $id => $errmsg ) {
      echo "ci_error{errorid=\"" . $id . "\",error=\"" . $errmsg . "\"} 1\n";
    }
  } else if( APIKEY == "" ) {
    echo "ci_error{errorid=\"401\",error=\"Empty Luno API Key\"} 1\n";
  } else if( APISEC == "" ) {
    echo "ci_error{errorid=\"403\",error=\"Empty Luno API Secret\"} 1\n";
  } else {
    // default, no errors
    echo "ci_error{errorid=\"0\",error=\"No Errors\"} 0\n";
  }
}

// // simple debugging, show all targets
// echo "\n<pre>";
// print_r( $targets );
// echo "</pre>";

exit;

?>
