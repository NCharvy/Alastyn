<?php

namespace Alastyn\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PicoFeed\Reader\Reader;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    /**
     * @Route("/admin")
     * @Template()
     */
    public function indexAction(Request $request)
    {
    	$reader = new Reader;
        $file=file_get_contents('Fichier_Sauvegarde_Lien_RSS/listeLiens.json');
        $datas=json_decode($file);

        if ($request->getMethod() == 'POST')
        {
            $name = $_POST['name'];
            $rss = $_POST['rss_link'];
            
            $datas[] = array('id'=>(count($datas)),'site'=>$name,'rss'=>$rss); 
            $textResponse = json_encode($datas,JSON_PRETTY_PRINT);
            file_put_contents('Fichier_Sauvegarde_Lien_RSS/listeLiens.json', $textResponse);

            $file=file_get_contents('Fichier_Sauvegarde_Lien_RSS/listeLiens.json');
            $datas=json_decode($file);
        }

        $resources = [];
        foreach ($datas as $data) {
            $resources[] = $data->rss;
        }
        $feeds = [];

        foreach ($resources as $rss) {
            $resource = $reader->download($rss);

            $parser = $reader->getParser(
                $resource->getUrl(),
                $resource->getContent(),
                $resource->getEncoding()
            );

            $feed = $parser->execute();

            $result = [];
            for($i = 0;$i<count($feed->items);$i++) {
                preg_match('/(\<img).*((\/\>)|(\<\/img))/',$feed->items[$i]->content,$result);
                if (count($result) > 0) {
                    $feed->items[$i]->preimage=$result[0];
                }else{
                    $feed->items[$i]->preimage="";
                }
            }

            $feeds[] = $feed;
        }

        return array('feeds' => $feeds);
    }

    /**
     * @Route("/add_rss")
     * @Template()
     */
    public function addRssAction(Request $request)
    {

        $contentFile=file_get_contents("Fichier_Sauvegarde_Lien_RSS/listeLiens.json");
        $array=json_decode($contentFile);

        if ($request->getMethod() == 'POST') 
        {
            $URL_SITE_NAME = $_POST["URL_VALEUR_NAME"];
            $URL_SITE_RSS = $_POST["URL_VALEUR_RSS"];

            
            array_push($array,array("id"=>(count($array)),"site"=>$URL_SITE_NAME,"rss"=>$URL_SITE_RSS)); 
            $textResponse = json_encode($array,JSON_PRETTY_PRINT);
            file_put_contents("Fichier_Sauvegarde_Lien_RSS/listeLiens.json", $textResponse);
            return array('file' => $array); 
        }else{
            return array('file' => $array); 
        }
    
        /*$vari="Fichier_Sauvegarde_Lien_RSS/Fichier_Lien_RSS.xml";

        if ($request->getMethod() == 'POST') 
        {
            $URL_SITE_NAME = $_POST["URL_VALEUR_NAME"];
            $URL_SITE_RSS = $_POST["URL_VALEUR_RSS"];
            $xml= simplexml_load_file($vari);
            $json = json_encode($xml);
            $array = json_decode($json,TRUE);
            array_push($array["site_web"],array("id"=>(count($array["site_web"])),"site"=>$URL_SITE_NAME,"rss"=>$URL_SITE_RSS));
            
            $_xml ="<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n";
            $_xml .="<flux>\r\n";
            
            for ($i = 0; $i < count($array["site_web"]); $i++)
            {
                $_xml .="   <site_web>\r\n\r\n";
                $_xml .="       <id>".$array["site_web"][$i]["id"]."</id>\r\n";
                $_xml .="       <site>".$array["site_web"][$i]["site"]."</site>\r\n";
                $_xml .="       <rss>".$array["site_web"][$i]["rss"]."</rss>\r\n\n";
                $_xml .="   </site_web>\r\n\r\n";            
            }
            
            $_xml .="</flux>\r\n";
            
            $file= fopen($vari, "w");
            fwrite($file,$_xml);
            fclose($file);

            return array('fichier_xml' => $array);          
        }
        else
        {
            $xml= simplexml_load_file($vari);
            $json = json_encode($xml);
            $array = json_decode($json,TRUE);
            return array('fichier_xml' => $array); 
        }
        */
    }
}
