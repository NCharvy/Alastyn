<?php
namespace Alastyn\AdminBundle\Services;

class CheckRSS
{
    public function checkRss($feed)
    {
            // create curl resource
            $curl = \curl_init();

            // set url
            curl_setopt($curl, CURLOPT_URL, $feed);

            //return the transfer as a string
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            // $output contains the output string
            $content = curl_exec($curl);

            // close curl resource to free up system resources
            curl_close($curl);


                if($content != '') 
                {
                    libxml_use_internal_errors(true);
                    $doc = new \DOMDocument('1.0', 'UTF-8');
                    $doc->load($content);
                    $errors = libxml_get_errors();

                    if(strstr($content,"404")==false)
                    {
                        if(empty($errors) || $errors[0]->level < 3) 
                        {
                            $message = 'Valide';
                        }
                        else 
                            {

                                if ($doc->validate()) 
                                {
                                    $lines = explode('\r', $content);
                                    $line = $lines[($errors[0]->line)-1];
                                    $message = $errors[0]->message .' at line '.$errors[0]->line.': '.htmlentities($line);
                                } 
                                else 
                                {
                                    if($errors[0]->message == null)
                                    {
                                        $message = 'Format du Document invalide ou page introuvable';
                                    }
                                    else
                                    {
                                       $message = $errors[0]->message; 
                                    }
                                }
                            }
                    }
                    else
                    {
                        $message = 'page introuvable';
                    }   

                }
                else
                {
                    $message = 'page introuvable';
                }
            
        return $message;
    }
}