<?php

namespace Tianrang\Boingpay;

use BoingPay;

/**
*  好收支付
*/
class PayClient extends AnotherClass
{

	private $app_id;

	private $mch_app_id;

	private $front_url;

	private $back_url;

	private $server_url;

	private $api_version;

	private $rsa_pub_path;

	private $rsa_path;

	private $rsa_pwd;

	private $dsa_pub_path;

	private $dsa_path;

	private $dsa_pwd;

	
	public function __construct()
	{
		$config = config('boingpay');

		$this->app_id       = $config['app_id'];
		$this->mch_app_id   = $config['mch_app_id'];
		$this->front_url    = $config['front_url'];
		$this->back_url     = $config['back_url'];
		$this->server_url   = $config['server_url'];
		$this->api_version  = $config['api_version'];

		$this->rsa_path     = $config['rsa_path'];
		$this->rsa_pub_path = $config['rsa_pub_path'];
		$this->rsa_pwd      = $config['rsa_pwd'];
		$this->dsa_path     = $config['dsa_path'];
		$this->dsa_pub_path = $config['dsa_pub_path'];
		$this->dsa_pwd      = $config['dsa_pwd'];

	}

	public function __init()
	{
		BoingPay::setAppId($this->app_id);

		BoingPay::setServerUrl($this->server_url);

		BoingPay::setApiVersion($this->api_version);

		## DSA
		BoingPay::setDSAKey($this->dsa_pub_path, $this->dsa_path, $this->dsa_pwd);

		## RSA
		BoingPay::setRSAKey($this->rsa_pub_path, $this->rsa_pub_path, $this->rsa_pwd);

		$this->payServ = BoingPay::getService();
	}

	/**
	 * APP支付，返回pay_token
	 */
	public function requestPay(array $params)
	{
		$result = $this->payServ->requestPay($params);

		return $result;
	}
}
