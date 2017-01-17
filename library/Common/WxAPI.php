<?php
namespace Common;

class WxAPI
{
	private $appKey;
	private $appSecret;
	private $httpClient;

	public function __construct($appKey, $appSecret)
	{
		$this->appKey = $appKey;
		$this->appSecret = $appSecret;
		$this->httpClient = new HttpClient();
	}
	
	public function request($url, $data)
	{
		$data['app_key'] = $this->appKey;
		$data['app_secret'] = $this->appSecret;
		
		$this->httpClient->setUrl($url);
		return $this->httpClient->get($data);
	}
	
	public function getErrMsg()
	{
		return $this->httpClient->getErrMsg();
	}
}