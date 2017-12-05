<?php

date_default_timezone_set('PRC');

require "../src/BoingPay.php";
require "../src/PayClient.php";

function config($val='')
{
	$config = require "../config/boingpay.php";
	return $config;
}

$pay = new \Tianrang\Boingpay\PayClient;

$params = [
	//商户订单号  必选
	"out_order_no" => "1111226642499654",
	//订单名称   必选
	"order_name" => "测试商品test",
	//支付金额    必选
	"pay_amount" => "1",

	"order_desc" => "diy - APP测试",
	//用户IP  必选
	"user_ip" => "192.168.10.22",
];

$pay_token = $pay->requestPay($params);

echo "<pre>";

print_r($pay_token);