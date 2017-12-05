<?php

namespace Tianrang\Boingpay;

class BoingPay {
	//应用ID
	public static $appId;
	//DSA私钥文件路径
	public static $DSAPrivateKeyFilePath;
	//DSA私钥密碼
	public static $DSAPrivateKeyFilePassword;
	//DSA公钥文件路径
	public static $DSAPublicKeyFilePath;
	//RSA私钥文件路径
	public static $RSAPrivateKeyFilePath;
	//RSA私钥密碼
	public static $RSAPrivateKeyFilePassword;
	//RSA公钥文件路径
	public static $RSAPublicKeyFilePath;
	//网关
	public static $gatewayUrl = "http://soa.boingpay.com/service/soa";
	//api版本
	public static $apiVersion = "2.0";
	// 表单提交字符集编码
	public static $postCharset = "UTF-8";
	// 文件字符集编码
	private static $fileCharset = "UTF-8";
	//签名类型
	public static $signType = "DSA";
	
	const STATUS_OK   = 'OK';
	const ERR_MESSAGE = 'message';
	const SIGNED_VALUE= 'result';
	
	private $bizContent;
	private $apiMethodName;
	static $bop;
	
	public static function getCashier() {
		if (empty(self::$bop)) {
			self::$bop = new BoingPay();
		}
		return self::$bop;
	}
	
	public static function getService() {
		if (empty(self::$bop)) {
			self::$bop = new BoingPay();
		}
		return self::$bop;
	}
	
	public function requestPay($param)
	{
		$this->bizContent = json_encode($param);
		$this->apiMethodName = 'cod.pay.order.request_pay';
		$result = $this->execute();
		if ($result['status'] == self::STATUS_OK) {
			//解密并重新加密sign_key参数
			$sign_key = self::$bop->rsaDecrypt($result['result']['pay_token']['sign_key']);
			$result['result']['pay_token']['sign_key'] = $sign_key;
			//解密并重新加密encrypt_key参数
			$encrypt_key = self::$bop->rsaDecrypt($result['result']['pay_token']['encrypt_key']);
			$result['result']['pay_token']['encrypt_key'] = $encrypt_key;
			$result['result']['pay_token']['app_id'] = self::$appId;
			return $result['result']['pay_token'];
		}
		return $result;
		
	}

	public function getPayStatus($app_id, $orderNo)
	{
		$param["mch_app_id"] = $app_id;
		$param["order_no"] = $orderNo;
		$this->bizContent = json_encode($param);
		$this->apiMethodName = 'cod.pay.order.pay_status';
		$result = $this->execute(); 
		return $result;
	}

	public function barcodeScanPay($param)
	{
		$this->bizContent = json_encode($param);
		$this->apiMethodName = 'cod.pay.user_payment_code.scan';
		$result = $this->execute(); 
		return $result;
	}

	public function refund($param)
	{
		$this->bizContent = json_encode($param);
		$this->apiMethodName = 'cod.pay.order_refund';
		$result = $this->execute(); 
		return $result;
	}

	public function getRefundStatus($appId, $orderNo, $refundOrderNo)
	{
		$param["mch_app_id"] = $appId;
		$param["order_no"] = $orderNo;
		$param["refund_order_no"] = $refundOrderNo;
		$this->bizContent = json_encode($param);
		$this->apiMethodName = 'pay.order.refund_status';
		$result = $this->execute(); 
		return $result;
	}

	public function close($appId, $orderNo)
	{
		$param["mch_app_id"] = $appId;
		$param["order_no"] = $orderNo;
		$this->bizContent = json_encode($param);
		$this->apiMethodName = 'cod.pay.order.close';
		$result = $this->execute(); 
		return $result;
	}

	public function reverse($appId, $orderNo)
	{
		$param["mch_app_id"] = $appId;
		$param["order_no"] = $orderNo;
		$this->bizContent = json_encode($param);
		$this->apiMethodName = 'cod.pay.order.reverse';
		$result = $this->execute(); 
		return $result;
	}

	public function createPayQRCode($param)
	{
		$this->bizContent = json_encode($param);
		$this->apiMethodName = 'cod.pay.order.qr_code.create';
		$result = $this->execute(); 
		return $result;
	}

	public function call($service, $args)
	{
		$this->bizContent = json_encode($args);
		$this->apiMethodName = $service;
		$result = $this->execute(); 
		return $result;
	}

	public function getNotifyResult($request)
	{
		//获取签名
		$sign = $request['sign'];
		//组装签名原串
		$signStr = $request['app_id'].$request['timestamp'].$request['version'].$request['event'];
		//验签
		$seccess = $this->verify($signStr, $sign, self::$signType);
		if(!$seccess) {
			throw new Exception("返回值签名验证失败");
		}
		return $request;
	}
	
	public static function setAppId($appId) {
		return self::$appId = $appId;
	}
	
	public static function setServerUrl($gatewayUrl) {
		return self::$gatewayUrl = $gatewayUrl;
	}
	
	public static function setApiVersion($apiVersion) {
		return self::$apiVersion = $apiVersion;
	}
	
	public static function setDSAKey($DSAPublicKeyFilePath, $DSAPrivateKeyFilePath, $DSAPrivateKeyFilePassword) {
		self::$DSAPublicKeyFilePath = $DSAPublicKeyFilePath;
		self::$DSAPrivateKeyFilePath = $DSAPrivateKeyFilePath;
		self::$DSAPrivateKeyFilePassword = $DSAPrivateKeyFilePassword;
	}
	
	public static function setRSAKey($RSAPublicKeyFilePath, $RSAPrivateKeyFilePath, $RSAPrivateKeyFilePassword) {
		self::$RSAPublicKeyFilePath = $RSAPublicKeyFilePath;
		self::$RSAPrivateKeyFilePath = $RSAPrivateKeyFilePath;
		self::$RSAPrivateKeyFilePassword = $RSAPrivateKeyFilePassword;
	}

	public function execute($authToken = null, $appInfoAuthtoken = null) {
		//  如果两者编码不一致，会出现签名验签或者乱码
		if (strcasecmp(self::$fileCharset, self::$postCharset)) {

			// writeLog("本地文件字符集编码与表单提交编码不一致，请务必设置成一样，属性名分别为postCharset!");
			throw new Exception("文件编码：[" . self::$fileCharset . "] 与表单提交编码：[" . self::$postCharset . "]两者不一致!");
		}
		$iv = self::$apiVersion;

		//组装系统参数
		$sysParams["app_id"] = self::$appId;
		$sysParams["version"] = $iv;
		$sysParams["service"] = $this->apiMethodName;
		$sysParams["timestamp"] = time();

		//获取业务参数
		$sysParams["params"] = $this->bizContent;
		//签名
		$sysParams["sign"] = $this->generateSign($sysParams, self::$signType);

		$result = $this->request2($sysParams);
		$result = $this->checkResult($result);
		return $result;
	}
	
	private function request2($args){
		global $log;
		$ch = curl_init () ;
		curl_setopt($ch, CURLOPT_URL, self::$gatewayUrl);

		$sb = '';
		$reqbody = array();
		foreach($args as $entry_key => $entry_value){
			$sb .= $entry_key.'='.urlencode($entry_value).'&';
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sb);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-length', count($reqbody)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$result = curl_exec($ch);
		
		curl_close($ch);
		return $result;
	}
	
	/**
	 * 检查服务调用结果是否正确，如果服务调用失败，则抛出失败异常
	 * @param result 服务调用的返回结果对象
	 * @throws Exception
	 */
	private function checkResult($result){
		global $log;
		$arr = json_decode($result, true);
		$response = json_decode($arr['response'], true);
		$sign = $arr['sign'];
		if($sign != null){
 			$seccess = $this->verify($arr['response'], $sign, self::$signType);
			if(!$seccess)
				throw new Exception("返回值签名验证失败");
		}
		return $response;
	}

	/**
	 * 校验$value是否非空
	 *  if not set ,return true;
	 *    if is null , return true;
	 **/
	protected function checkEmpty($value) {
		if (!isset($value))
			return true;
		if ($value === null)
			return true;
		if (trim($value) === "")
			return true;

		return false;
	}

	//验签
	function verify($data, $sign, $signType = 'RSA') {
		//读取公钥文件
		$pubKey = file_get_contents(self::$DSAPublicKeyFilePath);
		//转换为openssl格式密钥
		$res = openssl_get_publickey($pubKey);

		($res) or die('公钥错误。请检查公钥文件格式是否正确');  

		//调用openssl内置方法验签，返回bool值

		if ("RSA2" == $signType) {
			$result = openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
		} else if ("DSA" == $signType) {
			$result = openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_DSS1);
		} else {
			$result = openssl_verify($data, base64_decode($sign), $res);
		}
		if ($result == 1) {
			$result = true;
		} else {
			$result = false;
		}
		if(!$this->checkEmpty(self::$DSAPublicKeyFilePath)){
			openssl_free_key($res);
		}
		return $result;
	}

	public function generateSign($params, $signType = "DSA") {
		return $this->sign($this->getSignContent($params), $signType);
	}

	protected function getSignContent($params) {
		$stringToBeSigned = '';
		if(array_key_exists("app_id", $params)) {
			$stringToBeSigned .= $params['app_id'];
		}
		if(array_key_exists("timestamp", $params)) {
			$stringToBeSigned .= $params['timestamp'];
		}
		if(array_key_exists("version", $params)) {
			$stringToBeSigned .= $params['version'];
		}
		if(array_key_exists("service", $params)) {
			$stringToBeSigned .= $params['service'];
		}
		if(array_key_exists("params", $params)) {
			$stringToBeSigned .= $params['params'];
		}
		//$stringToBeSigned = $params['app_id'] . $params['timestamp'] . $params['version'] . $params['service'] . $params['params'];
		$stringToBeSigned = str_replace("\r\n", "", $stringToBeSigned);
		return $stringToBeSigned;
	}

	protected function sign($data, $signType = "DSA") {
		if ("DSA" == $signType) {
			$file = file_get_contents(self::$DSAPrivateKeyFilePath);
			openssl_pkcs12_read($file, $res, self::$DSAPrivateKeyFilePassword);
		} else {
			$priKey = file_get_contents(self::$RSAPrivateKeyFilePath);
			$res = openssl_get_privatekey($priKey, self::$RSAPrivateKeyFilePassword);
		}

		($res) or die('您使用的私钥格式错误，请检查私钥配置');
		if ("RSA2" == $signType) {
			openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
		} else if ("DSA" == $signType) {
			openssl_sign($data, $sign, $res['pkey'], OPENSSL_ALGO_DSS1);
		} else {
			openssl_sign($data, $sign, $res);
		}
		if("DSA" != $signType && !$this->checkEmpty(self::$DSAPrivateKeyFilePath)){
			openssl_free_key($res);
		}
		$sign = base64_encode($sign);
		return $sign;
	}

	public function rsaEncrypt($data) {
		//读取公钥文件
		$pubKey = file_get_contents(self::$RSAPublicKeyFilePath);
		//转换为openssl格式密钥
		$res = openssl_get_publickey($pubKey);
		if (!openssl_public_encrypt($data, $chrtext, $res, OPENSSL_PKCS1_PADDING)) {
			echo "<br/>" . openssl_error_string() . "<br/>";
		}
		return base64_encode($chrtext);
	}

	public function rsaDecrypt($data) {

		//读取私钥文件
		$priKey = file_get_contents(self::$RSAPrivateKeyFilePath);
		//转换为openssl格式密钥
		openssl_pkcs12_read($priKey, $res, self::$RSAPrivateKeyFilePassword);
		$res1 = openssl_pkey_get_private($res['pkey']);
		if (!openssl_private_decrypt(base64_decode($data), $dcyCont, $res1, OPENSSL_PKCS1_PADDING)) {
			echo "<br/>---" . openssl_error_string() . "<br/>";
		}
		return $dcyCont;
	}
}