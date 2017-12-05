<?php

/**
 * 支付配置
 *
 * @author stone <[<shilei_zhang@163.com>]>
 */

return [

	'app_id'          => '790472744264278016',
	'server_url'      => 'http://116.62.36.139:9091/service/soa',
	'api_version'     => '2.0',

	## DSA密钥
	'dsa_public_key'  => './storage/pay/dsa_owner.pem',
	'dsa_private_key' => './storage/pay/dsa_partner.pfx',
	'dsa_private_pwd' => '123456',

	## RSA密钥
	'rsa_public_key'  => './storage/pay/rsa_owner.pem',
	'rsa_private_key' => './storage/pay/rsa_partner.pfx',
	'rsa_private_pwd' => '123456',

];
