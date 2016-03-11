<?php
namespace Alastyn\AdminBundle\Services;

class Service
{
    public function Service_verification_rss( $sFeedURL )
	{

		if (@fopen($sFeedURL, 'r')) 
		{

		    $sValidator = 'https://validator.w3.org/feed/check.cgi?url=';
		    
		    if( $sValidationResponse = @file_get_contents($sValidator . urlencode($sFeedURL)) )
		    {
		        if( stristr( $sValidationResponse , 'This is a valid RSS feed' ) !== false )
		        {
		            return "FLUX RSS VALIDER ".$sValidationResponse;
		        }
		        else
		        {
		            return "XML INCORRECT";
		        }
		    }
		    else
		    {
		        return "ERREUR CONNECTION";
		    }
	    }
	    else
	    {
	    	return "URL INCORRECT";
	    }
	}
}