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

            if($content != '' && !strstr($content,"403") && !strstr($content,"404") && !strstr($content,"not found")) 
            {
                $content = file_get_contents($feed);

                // Réparation du xml
                // $tidy = \tidy_parse_string($content);
                // if($tidy->cleanRepair()) {
                //     $content = $tidy;
                // }

                libxml_use_internal_errors(true);
                $doc = new \DOMDocument('1.0', 'utf-8');
                $doc->loadXML($content);
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
                                $message = 'Valide';
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