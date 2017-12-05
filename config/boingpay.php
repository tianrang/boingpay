<?php

/**
 * 支付配置
 *
 * @author stone <[<shilei_zhang@163.com>]>
 */

return [

	'app_id'       => '936080432603078656',
	'server_url'   => 'http://soa.boingpay.com/service/soa',
	'api_version'  => '2.0',

	## DSA密钥
	'dsa_pub_path' => '../pay-cer/dsa_pub.pem',
	'dsa_path'     => '../pay-cer/dsa_private_key.pfx',
	'dsa_pwd'      => '651545',

	## RSA密钥
	'rsa_pub_path' => '../pay-cer/rsa_pub.pem',
	'rsa_path'     => '../pay-cer/rsa_private_key.pfx',
	'rsa_pwd'      => '651545',

	'mch_app_id'   => '936080432603078656',
	'front_url'    => 'http://ishappy.cn/front.html',
	'back_url'     => 'http://ishappy.cn/test.php',

];
