<?php
	namespace yuanshuai\yscomponents\aliyun\api;
	include_once 'Util/Autoloader.php';
	use HttpRequest;
	use HttpHeader;
	use HttpMethod;
	use ContentType;
	use SystemHeader;
	use HttpClient;
	/**
	* 阿里云API工具类
	*/
	class AliyunApi extends \yii\base\Component{
		public $appKey;
		public $appSecret;
		public $host;
		public $path;
		public $appCode;
		private $headerType = HttpHeader::HTTP_HEADER_CONTENT_TYPE;
		private $contentType = ContentType::CONTENT_TYPE_TEXT;
		private $systemHeader = SystemHeader::X_CA_SIGNATURE;
		/**
		 * get方式请求
		 * @param  string $path   请求地址
		 * @param  array  $params 请求参数
		 * @param  array  $header header参数
		 */
		public function get($params=array(),$header=array(),$signHeader=array(),$path=''){
			if ($path) {
				$this->path = $path;
			}
			$request = new HttpRequest($this->host, $this->path, HttpMethod::GET, $this->appKey, $this->appSecret);
			$request->setHeader($this->headerType,$this->contentType);
			$request->setHeader(HttpHeader::HTTP_HEADER_ACCEPT, ContentType::CONTENT_TYPE_TEXT);
			//设置header
			$request->setHeader("Authorization:APPCODE",$this->appCode);
			foreach ($header as $key => $value) {
				$request->setHeader($key,$value);
			}
			//设置参数查询
			foreach ($params as $key => $value) {
				$request->setQuery($key,$value);
			}
			//指定参与签名的header
			$request->setSignHeader($this->systemHeader);
			foreach ($signHeader as $key => $value) {
				$request->setSignHeader($value);
			}
			$response = HttpClient::execute($request);
			return $this->foramt($response);
		}
		/**
		 * 格式化接口数据
		 */
		public function foramt($response){
			$content = $response->getContent();
			$result = explode("\n",$content);
			return $result[count($result) - 1];
		}
		/**
		 * 设置headerType:Accept,Content-MD5,Content-Type,User-Agent,Date
		 */
		public function setHeaderType($headerType){
			$this->headerType = $headerType;
		}
		/**
		 * 设置contentType:
		 * application/x-www-form-urlencoded; charset=UTF-8
		 * application/octet-stream; charset=UTF-8
		 * application/json; charset=UTF-8
		 * application/xml; charset=UTF-8
		 * application/text; charset=UTF-8
		 */
		public function setContentType($contentType){
			$this->contentType = $contentType;
		}
		/**
		 * 设置systemHeader签名：
		 * X-Ca-Signature：签名Header
		 * X-Ca-Signature-Headers：所有参与签名的Header
		 * X-Ca-Timestamp：请求时间戳
		 * X-Ca-Nonce：请求放重放Nonce,15分钟内保持唯一,建议使用UUID
		 * X-Ca-Key：APP KEY
		 */
		public function setSystemHeader($systemHeader){
			$this->systemHeader = $systemHeader;
		}
	}
?>