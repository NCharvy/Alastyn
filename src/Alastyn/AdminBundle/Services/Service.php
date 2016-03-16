<?php
namespace Alastyn\AdminBundle\Services;

class Service
{
    public function checkRss($feed)
	{

		if (@fopen($feed, 'r')) 
		{
				$feed = file_get_contents($feed);
				libxml_use_internal_errors(true);
				$doc = new \DOMDocument('1.0', 'utf-8');
				$doc->loadXML($feed);

				$errors = libxml_get_errors();
				if (empty($errors))
				{
				    return "Valide";
				}

				$error = $errors[ 0 ];
				if ($error->level < 3)
				{
				    return "Valide";
				}

				$lines = explode("r", $feed);
				$line = $lines[($error->line)-1];

				return $message = $error->message . ' at line ' . $error->line . ': ' . htmlentities($line);
	    }
	    else
	    {
	    	return "URL incorrect";
	    }
	}
}