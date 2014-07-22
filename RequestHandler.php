<?php

/**
 * Class RequestHandler
 * Processes all request to the app
 */
class RequestHandler
{
	//The action being taken
	protected $action;

	//Any GET/POST parameters supplied with the request
	protected $params;

	public function __construct(array $request)
	{
		$this->parseRequest($request);
	}

	/**
	 * Parses the request
	 * @param $request
	 */
	private function parseRequest($request)
	{

		if(isset($request['action']))
		{
			$this->action = $request['action'];
			unset($request['action']);
		}

		$this->params = $request;
	}

	/**
	 * Calls a method based on the request action parameter
	 * @param null $action
	 * @return mixed
	 * @throws Exception
	 */
	public function callAction($action = null)
	{
		$method = $action ?: $this->action;

		if(is_null($method))
		{
			throw new Exception('No action supplied!');
		}

		return $this->{$method}();
	}

	/**
	 * Handles a conversion request when a form is submitted
	 * with a Formisimo integration
	 * @return bool
	 */
	public function trackConversion()
	{
		$rawRequest = $this->decodeParam('rawRequest');

		$transporter = new ConversionTransporter($rawRequest);

		return $transporter->run();
	}

	/**
	 * Handles a remove integration request. For when
	 * a Jotform user removes the Formisimo integration from form
	 * @return bool
	 * @throws Exception
	 */
	public function removeIntegration()
	{
		$formID = $this->getParam('formID');
		$apiKey = $this->getParam('apiKey');

		if (is_null($formID))
		{
			throw new Exception('Form ID is required.');
		}

		$jotform = $this->initJotform($apiKey);

		$destroyer = new WebhookDestroyer($jotform, $formID);
		return $destroyer->run();
	}

	/**
	 * Retrieves a request parameter
	 * @param $key
	 * @param null $default
	 * @return null
	 */
	protected  function getParam($key, $default = null)
	{
		if(isset($this->params[$key]))
		{
			return $this->params[$key];
		}

		return $default;
	}

	/**
	 * Find a request parameter and run it through json_decode
	 * @param $key
	 * @param array $default
	 * @return array|mixed
	 */
	private function decodeParam($key, $default = array())
	{
		$param = $this->getParam($key);

		if(is_null($param)) return $default;

		/*
		* Strips extra slashes in JSON
		* Added because of weird formatting in request data .
	 	* If I can figure out the reason for that we can remove this.
		*/
		$data = json_decode(stripslashes($param), true);

		if ( ! is_null($data)) return $data;

		$data = json_decode($param, true);

		return is_null($data) ? $default : $data;
	}

	private function initJotform($apiKey = null)
	{
		if (is_null($apiKey))
		{
			throw new Exception('Api Key is required for JotForm api.');
		}

		return new JotForm($apiKey);
	}

	/**
	 * Throws exception for any unknown request action
	 * @param $method
	 * @param $parameters
	 * @throws Exception
	 */
	public function __call($method, $parameters)
	{
		throw new Exception('Unknown Action.');
	}
}
