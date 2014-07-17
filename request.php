<?php
	try
	{
		require 'vendor/autoload.php';
		$request = new RequestHandler($_REQUEST);
		return $request->callAction();
	}
	catch (Exception $e)
	{
		//Once complete switch this to log files instead of emails
		return mail('paul@jotform.com' , 'Formisimo Integration Failed' , $e->getMessage(), 'From: Paul Simpson <paul@jotform.com>' . "\r\n");
	}
