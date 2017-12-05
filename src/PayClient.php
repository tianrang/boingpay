<?php

namespace Tianrang\Boingpay;

use Tianrang\Boingpay\BoingPay;

/**
*  好收支付
*/
class PayClient
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
		$this->api_version  = $config['api_version'];
		$this->server_url   = $config['server_url'];
		
		$this->mch_app_id   = $config['mch_app_id'];
		$this->front_url    = $config['front_url'];
		$this->back_url     = $config['back_url'];

		$this->rsa_path     = $config['rsa_path'];
		$this->rsa_pub_path = $config['rsa_pub_path'];
		$this->rsa_pwd      = $config['rsa_pwd'];

		$this->dsa_path     = $config['dsa_path'];
		$this->dsa_pub_path = $config['dsa_pub_path'];
		$this->dsa_pwd      = $config['dsa_pwd'];

		$this->__init();

	}

	public function __init()
	{
		BoingPay::setAppId($this->app_id);

		// BoingPay::setServerUrl($this->server_url);

		BoingPay::setApiVersion($this->api_version);

		## DSA
		BoingPay::setDSAKey($this->dsa_pub_path, $this->dsa_path, $this->dsa_pwd);

		## RSA
		BoingPay::setRSAKey($this->rsa_pub_path, $this->rsa_pub_path, $this->rsa_pwd);
	}

	public function getServ()
	{
		return BoingPay::getService();
	}

	/**
	 * APP支付，返回pay_token
	 */
	public function requestPay(array $params)
	{	
		$base   = [
			'mch_app_id' => $this->mch_app_id,
			'front_url'  => $this->front_url,
			'back_url'   => $this->back_url,
		];

		$params = array_merge($params, $base);

		$result = $this->getServ()->requestPay($params);

		return $result;
	}
}
