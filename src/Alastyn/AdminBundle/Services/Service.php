<?php
namespace Alastyn\AdminBundle\Services;

class Service
{
    public function Service_verification_rss( $xmlContent )
	{

		if (fopen($xmlContent, 'r')) 
		{
				$xmlContent = file_get_contents($xmlContent);
				libxml_use_internal_errors(true);
				$doc = new \DOMDocument('1.0', 'utf-8');
				$doc->loadXML($xmlContent);

				$errors = libxml_get_errors();
				if (empty($errors))
				{
				    return true;
				}

				$error = $errors[ 0 ];
				if ($error->level < 3)
				{
				    return true;
				}

				$lines = explode("r", $xmlContent);
				$line = $lines[($error->line)-1];

				return $message = $error->message . ' at line ' . $error->line . ': ' . htmlentities($line);
	    }
	    else
	    {
	    	return "URL INCORRECT";
	    }
	}
}