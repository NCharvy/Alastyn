<?php
namespace Alastyn\AdminBundle\Services;

class CheckRSS
{
    public function checkRss($feed)
    {
        $message = '';
        $file = @fopen($feed, 'r');
        $http = @get_headers($feed)[0];

        if ($http == 'HTTP/1.0 403 Forbidden') {
            $message = 'Erreur HTTP : 403 Forbidden';
        } else if($file) {
            $content = file_get_contents($feed);
            libxml_use_internal_errors(true);
            $doc = new \DOMDocument('1.0', 'utf-8');
            $doc->loadXML($content);

            $errors = libxml_get_errors();
            if(empty($errors) || $errors[0]->level < 3) {
                $message = 'Valide';
            } else {
                if ($http == 'HTTP/1.0 200 OK') {
                    $message = 'Flux malformé';
                } else if ($doc->validate()) {
                    $lines = explode('\r', $content);
                    $line = $lines[($errors[0]->line)-1];
                    $message = $errors[0]->message .' at line '.$errors[0]->line.': '.htmlentities($line);
                } else {
                    $message = 'Format du Document invalide';
                }

            }

            fclose($file);
        } else {
            if ($http == 'HTTP/1.0 200 OK') {
                $message = "Flux malformé";
            } else {
                $message = 'URL incorrect';
            }
        }

        return $message;
    }
}