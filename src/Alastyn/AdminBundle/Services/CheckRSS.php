<?php
namespace Alastyn\AdminBundle\Services;

class CheckRSS
{
    // L'algorithme utilisant le site s'occupant de vérifier et de corriger les flux rss
    // se trouve dans le fichier CheckRssFeedValidator.php dans ce même dossier


    public function checkRss($feed) {
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
}