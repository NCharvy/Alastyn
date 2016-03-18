<?php
namespace Alastyn\AdminBundle\Services;

class CheckRSS
{
    public function checkRss($feed)
    {
        $message = '';
        $file = @fopen($feed, 'r');

        if($file) {
            $content = file_get_contents($feed);
            libxml_use_internal_errors(true);
            $doc = new \DOMDocument('1.0', 'utf-8');
            $doc->loadXML($content);

            $errors = libxml_get_errors();
            if(empty($errors) || $errors[0]->level < 3) {
                $message = 'Valide';
            } else {            
                $lines = explode('\r', $content);
                $line = $lines[($error->line)-1];

                $message = $error->message .' at line '.$error->line.': '.htmlentities($line);
            }

            fclose($file);
        } else {
            $message = 'URL incorrect';
        }

        return $message;
    }
}