<?php
$config = ['config' => 'C:/xampp/php/extras/ssl/openssl.cnf', 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
$res = openssl_pkey_new($config);
openssl_pkey_export($res, $private_key, null, $config);
var_dump($private_key !== null);
