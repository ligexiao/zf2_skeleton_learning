<?php
namespace Common;

class HttpClient
{
	private $url;
	private $errMsg;
	private $ch;
	private $options;

	public function __construct($url = '', $options = array())
	{
		$this->url = $url;		
		if (!empty($url)) {
			$this->ch = curl_init($url);
		}
		
		$this->options = $options;
	}
	
	public function getErrMsg()
	{
		return !empty($this->errMsg) ? $this->errMsg : curl_error($this->ch);
	}
	
	public function setUrl($url)
	{
		$this->url = $url;
		if (!empty($this->ch)) {
			curl_close($this->ch);
			$this->ch = curl_init($url);
		} else {
			$this->ch = curl_init($url);
		}
	}

	public function postJson($data, $options = array())
	{
		$this->options += array(
				CURLOPT_HTTPHEADER => array(
						'Content-type: application/json'
				),
		);

		return $this->post($data, $this->options);
	}
	
	public function post($data, $options = array(), $retry_times=3)
	{
		$this->options += array(
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_RETURNTRANSFER => true,
		        CURLOPT_CONNECTTIMEOUT => 5,
			);
		curl_setopt_array($this->ch, $this->options);

        $retries=0;
        do 	{
            $response = curl_exec($this->ch);
            $http_status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            if (curl_errno($this->ch)) {
                $error_msg = '[' . curl_error($this->ch) .'-'. curl_errno($this->ch) . "]";
            }
        } while ($http_status!=200 && ++$retries<$retry_times);
        Log::info("response:".$response);
        $result = array();
        if (curl_errno($this->ch)){
            $result['code']=-1;
            $result['msg']=$error_msg;
            Log::info("err_msg:".$error_msg);
        }else{
            $data = json_decode($response,true);
            if(!empty($data) && is_array($data)) {
                $result['code'] = 0;
                $result['data'] = $data;
            }else{
                $result['code']=-2;
                $result['msg']="返回结果格式错误";
                Log::info("err_msg:".$result['msg']);
            }
        }

        return $result;
	}
	
	public function get($data, $options = array(), $retry_times=3)
	{
		if (!empty($data)) {
			$data = http_build_query($data);
			if (strpos($this->url, '?') > 0) {
				$this->url .= '&' . $data;
			} else {
				$this->url .= '?' . $data;
			}
			$this->setUrl($this->url);
		}
		$this->options += array(
				CURLOPT_RETURNTRANSFER => true,
		        CURLOPT_CONNECTTIMEOUT => 5,
		);

		curl_setopt_array($this->ch, $this->options);

        $retries=0;
        do 	{
            $response = curl_exec($this->ch);
            $http_status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            if (curl_errno($this->ch)) {
                $error_msg = '[' . curl_error($this->ch) .'-'. curl_errno($this->ch) . "]";
            }
        } while ($http_status!=200 && ++$retries<$retry_times);
        Log::info("response:".$response);
        $result = array();
        if (curl_errno($this->ch)){
            $result['code']=-1;
            $result['msg']=$error_msg;
            Log::info("err_msg:".$error_msg);
        }else{
            $data = json_decode($response,true);
            if(!empty($data) && is_array($data)) {
                $result['code'] = 0;
                $result['data'] = $data;
            }else{
                $result['code']=-2;
                $result['msg']="返回结果格式错误";
                Log::info("err_msg:".$result['msg']);
            }
        }

        return $result;
	}
	
	public function __destruct()
	{
		if (!empty($this->ch)) {
			curl_close($this->ch);
		}
	}
	
	public function getStatusCode()
	{
		return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
	}
}