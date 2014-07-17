<?php

/**
 * Class ConversionTransporter
 * Class for transporting the conversion data from us to Formsimo
 */
class ConversionTransporter
{
	//The Formisimo conversion tracking url
	private $url = 'http://tracking.formisimo.com/conversion';

	//The submission data
	private $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function run()
	{
		//init curl
		$ch = curl_init($this->url);

		//Set required CURL options
		curl_setopt_array($ch, $this->getCurlOptions());

		//execute the CURL request
		$result = curl_exec($ch);

		//close curl
		curl_close($ch);

		if ($result === false)
		{
			throw new Exception('Curl Operation failed. Data: ' . json_encode($data));
		}

		return true;
	}

	/**
	 * Creates an array of curl options
	 * ready for sending to Formisimo
	 * @return array
	 */
	public function getCurlOptions()
	{
		$data = $this->getData();

		return array(
			CURLOPT_POST => count($data),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_POSTFIELDS => http_build_query($data)
		);
	}

	/**
	 * Builds the data we need to pass to Formisimo
	 * @return array
	 */
	private function getData()
	{
		$data = $this->getTrackingData();

		/*
		 * Add the conversion key to the data
		 * as Formisimo do in their conversion script
		 */
		$data['conversion'] = true;
		return $data;
	}

	/**
	 * Retrieves the Formisimo data from the submission
	 * which was created by the tracking.js file
	 * @return array
	 */
	public function getTrackingData()
	{
		$data = isset($rawRequest['formisimo-tracking'])
			? $rawRequest['formisimo-tracking']
			: array();

		return is_string($data)
			? (array) json_decode($data, true)
			: $data;
	}
}