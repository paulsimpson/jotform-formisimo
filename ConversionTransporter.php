<?php

/**
 * Class ConversionTransporter
 * Class for transporting the conversion data from us to Formsimo
 */
class ConversionTransporter
{
	//The Formisimo conversion tracking url
	private $url = 'http://tracking.formisimo.com/conversion';

	//The data required by Formisimo from the submission
	private $required = array('browsertime-milliseconds', 'browser-timezone', 'cookie', 'referrer');

	//The submission data
	private $data;

	/**
	 * @param array $data
	 */
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

		$data = $this->getFormisimoData();

		//Set required CURL options
		curl_setopt_array($ch, $this->getCurlOptions($data));

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
	public function getCurlOptions(array $data)
	{
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
	private function getFormisimoData()
	{
		//Get the data submitted by the tracking widget script
		$submitted = $this->getSubmittedData();

		/*
		 * We flip the required array so we can compare
		 * keys with the submitted data keys.
		 */
		$required = array_flip($this->required);

		/*
		 * Check whether the submitted data contains all of the required data.
		 * The $diff will let us know data is missing.
		 */
		$diff = array_diff_key($required, $submitted);

		/*
		 * If difference is not empty then we are missing some of the
		 * required data so stop execution and log the submitted data so we can debug.
		 */
		if ( ! empty($diff))
		{
			throw new Exception('Required Data Missing. Data: ' . json_encode($submitted));
		}

		/*
		 * Just to be safe we will remove any extra data included
		 * in the submitted data that is not required. This is an extra
		 * security measure to ensure we are not accidently passing any rogue
		 * data through to Formisimo
		 */
		$data = array_intersect_key($submitted, $required);

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
	public function getSubmittedData()
	{
		//Check we have received the Formisimo tracking data from the submission
		$data = isset($this->data['formisimo-tracking'])
			? $this->data['formisimo-tracking']
			: array(); //Default to empty array

		return ! is_array($data)
			? (array) json_decode($data, true) //cast array in case json_decode fails
			: $data; //Just return the array
	}
}