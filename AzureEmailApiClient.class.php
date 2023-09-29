<?

class AzureEmailApiClient {
	
	private $azureResourceName = 'unknown';
	
	private $accessKey = 'unknown';
	
	private $senderAddress = 'noreply@example.com';
	
	private $replyToAddress = 'noreply@example.com';
	
	private $replyToDisplayName = 'NoReply';
	
	public function __construct($details = []) {
		$this->configure($details);
	}
	
	public function configure($details) {
		
		if (isset($details['azureResourceName'])) {
			$this->azureResourceName = $details['azureResourceName'];
		}
		
		if (isset($details['accessKey'])) {
			$this->accessKey = $details['accessKey'];
		}
		
		if (isset($details['senderAddress'])) {
			$this->senderAddress = $details['senderAddress'];
		}
		
		if (isset($details['replyToAddress'])) {
			$this->replyToAddress = $details['replyToAddress'];
		}
		
		if (isset($details['replyToDisplayName'])) {
			$this->replyToDisplayName = $details['replyToDisplayName'];
		}
	}
	
	public function sendEmail($details) {
		
		$body = [
			"senderAddress" => $this->senderAddress,
			"content" => [
				"subject" => $details['subject'],
				"plainText" => $details['content']
			],
			"recipients" => [],
			"replyTo" => [
				[
					"address" => $this->replyToAddress,
					"displayName" => $this->replyToDisplayName
				]
			]
		];
		
		$body['recipients']['to'] = [];
		foreach ($details['to'] as $to) {
			$body['recipients']['to'][] = [
				'address' => $to['address'],
				'displayName' => $to['displayName']
			];
		}
					
		$bodyJson = json_encode($body, JSON_PRETTY_PRINT);

		$contentHash = base64_encode(hash('sha256', $bodyJson, true));

		$verb = 'POST';
		$authority = $this->azureResourceName.'.uk.communication.azure.com';
		$pathAndQuery = 'emails:send?api-version=2023-03-31';
		$fullUrl = "https://$authority/$pathAndQuery";
		$timestamp = gmdate('D, d M Y H:i:s \G\M\T', time());

		$stringToSign = "$verb\n/$pathAndQuery\n$timestamp;$authority;$contentHash";
		$signature = hash_hmac('sha256', $stringToSign, base64_decode($this->accessKey), true);
		$signatureBase64 = base64_encode($signature);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5); 
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_FAILONERROR, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $verb);
		curl_setopt($curl, CURLOPT_URL, $fullUrl);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			"Authorization: HMAC-SHA256 SignedHeaders=x-ms-date;host;x-ms-content-sha256&Signature=$signatureBase64",
			"Content-Type: application/json",
			"x-ms-date: $timestamp",
			"x-ms-content-sha256: $contentHash"	
		]);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $bodyJson);
		
		$result = curl_exec($curl);
		
		if (curl_errno($curl)) {
			$errorMessage = curl_error($curl);
			throw new Exception($errorMessage);
		}
		
		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($statusCode === 0) {
			throw new Exception("Web service call failed. Status code is $statusCode.");
		}
		
		$response = array(
			'statusCode' => $statusCode,
			'content' => json_decode($result, true)
		);
		curl_close($curl);

		return $response;
	}
	
}

?>