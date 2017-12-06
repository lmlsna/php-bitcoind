<?php

/**
 * JSONRPC 1.0/2.0 function wrapper using PHP-CURL
 *
 * This is a simple, single-function, jsonrpc 2.0 spec valid, wrapper for
 * PHP. It reqires the php-curl module to be installed and enabled in PHP.
 *
 * There are several MUCH more verbose jsonrpc wrappers for PHP, but
 * this straight-forward implementation is just fine for almost every
 * jsonrpc related thing you want to do in PHP (actually probably overkill).
 *
 * The fumction takes 1 required, and 4 optional rpc arguments. A sane and
 * spec compliant default of the current epoch time is used for 'id'
 * if you do not specify one. The jsonrpc version is set to '2.0' but works just as
 * well if you need to set it to '1.0'. An empty array is used if no params are
 * specified.
 *
 * The url can be passed (3rd arg), but prefers to be set by the JSONRPC_URL
 * constant (if always calling the same url). Also, you may include user, password,
 * scheme, and port in addition to just url, and those bit will go where needed.
 *
 * The $params arg must always be an array, so it tries to guess how to
 * do that if you pass it a string. Similarly, because json represents
 * integers and floats differently that string representations of the same,
 * (unlike PHP), it will try to turn them back into numbers if passed
 * as a stringy $param.
 *
 * Usage:
 *     $obj = jsonrpc('getblockhash', array(1), 'http://user:pass@127.0.0.1:9332/path', 'an id', '2.0' )
 *     $obj = jsonrpc('getblockhash', 1 ); // also works
 */

function jsonrpc( $method, $params=array(), $id='', $jsonrpc='1.0') {
  /** rpc query struct */
  $q = (object) [
    'method' => trim($method),
    'params' => (array) $params,
    'id' => trim( empty($id) ? '_' . microtime(true) : $id ),
   ];
  $q_json = json_encode($q);

  /** prepare to curl */
  $ch = curl_init();
  curl_setopt_array( $ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_USERPWD        => BTC_RPC_USER . ( empty(BTC_RPC_PWD) ? '' : ':' . BTC_RPC_PWD ),
    CURLOPT_POSTFIELDS     => $q_json,
    CURLOPT_ENCODING       => 'application/json',
    CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
    CURLOPT_URL            => sprintf( '%s://%s%s',
      BTC_RPC_PROTO,
      BTC_RPC_HOST,
      ( empty(BTC_RPC_PORT) ? '' : ':' . BTC_RPC_PORT )
    )
  ));

  // decode, close, return
  $result_json = curl_exec($ch);
  $result = json_decode($result_json);
  curl_close($ch);
  return $result->result;
}


// Get a new address
function getnewaddress( $account='' ) {
  $result = jsonrpc( 'getnewaddress', array( $account ) );
  return $result;
}


