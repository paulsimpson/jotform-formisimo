<?php

/**
 * Class WebhookDestroyer
 * Class for deleting the Submission webhook from
 * from JotForm server when Formisimo integrtaion is removed.
 */
class WebhookDestroyer
{
	private $url = 'http://formisimo.jotform.io/request.php?action=runWebhook';

	//JotForm Api instance
	private $jotform;

	private $formID;

	public function __construct(JotForm $jotform, $formID)
	{
		$this->jotform = $jotform;
		$this->formID = $formID;
	}

	/**
	 * @return bool
	 */
	public function run()
	{
		$index = $this->findWebhook();

		if ($index !== false)
		{
			$this->jotform->deleteFormWebhook($this->formID, $index);
		}

		return true;
	}

	/**
	 * Retrieves the webhook from JotForm
	 * @return mixed
	 */
	private function findWebhook()
	{
		$webhooks = $this->jotform->getFormWebhooks($this->formID);

		return array_search($this->url, $webhooks);
	}
}