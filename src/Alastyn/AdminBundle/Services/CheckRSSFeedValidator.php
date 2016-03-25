<?php
namespace Alastyn\AdminBundle\Services;

class CheckRSSFeedValidator
{
    // L'alogriothme utilisé actuellement sur l'application se trouve dans le fichier
    // CheckRss.php dans ce même dossier

    public function checkRSSFeedValidator($feed)
    {
        $verification_aditionnel = 1;

        if($verification_aditionnel == 1 && $feed != "")
        {
                $sURL  = 'http://www.feedvalidator.org/check.cgi?url=' . urlencode($feed);
                $sPage = file_get_contents($sURL);
                 
                if (strstr($sPage, 'This is a valid RSS feed.')) 
                {
                    try
                    {
                        $curl = \curl_init();
                        // set url
                        curl_setopt($curl, CURLOPT_URL, $feed);
                        //return the transfer as a string
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_TIMEOUT,10);
                        // $output contains the output string
                        $content = curl_exec($curl);
                        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                        // close curl resource to free up system resources
                        curl_close($curl);

                        if($content != '')
                        {
                            libxml_use_internal_errors(true);
                            $doc = new \DOMDocument('1.0', 'utf-8');
                            $doc->loadXML($content);
                            $errors = libxml_get_errors();
                    
                            if($http_status != 404)
                            {
                                if(empty($errors) || $errors[0]->level < 3) 
                                {
                                    $message = 'Valide';
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
                        }
                        else
                        {
                            $message = 'page introuvable';
                        }


                    }
                    catch(Exeption $e)
                    {echo $e; die;}
                }
                else
                {
                    return 'page introuvable';
                }
        }
        else if($verification_aditionnel == 0)
        {
                // create curl resource
                $curl = \curl_init();
                // set url
                curl_setopt($curl, CURLOPT_URL, $feed);
                //return the transfer as a string
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_TIMEOUT,10);
                // $output contains the output string
                $content = curl_exec($curl);
                $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                // close curl resource to free up system resources
                curl_close($curl);

                $handle = @fopen($feed, 'r');

                if($content != '' && $handle && $feed) 
                {
                    /*
                    // Configuration de tidy
                    $config = array(
                        'indent'     => true,
                        'input-xml'  => true,
                        'output-xml' => true,
                        'wrap'       => false
                    );

                    // Réparation du xml
                    $tidy = new \Tidy();
                    $tidy->parseFile($feed, $config, 'utf8');
                    $tidy->cleanRepair();

                    // Permet de ne pas avoir l'erreur "timeout"
                    ini_set('max_execution_time', 0);
                    */

                    $content = file_get_contents($feed);

                    libxml_use_internal_errors(true);
                    $doc = new \DOMDocument('1.0', 'utf-8');
                    $doc->loadXML($content);
                    $errors = libxml_get_errors();
                
                    if($http_status != 404)
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

                    @fclose($handle);
                }
                else
                {
                    $message = 'page introuvable';
                }
                
            return $message;
        }
        else
        {
            return 'page introuvable';
        }
    }
}