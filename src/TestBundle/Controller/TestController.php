<?php

namespace TestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PicoFeed\Reader\Reader;

class TestController extends Controller
{
    /**
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $reader = new Reader;
        $resources = ['http://feeds.howtogeek.com/howtogeek',
         'http://unodieuxconnard.com/feed/',
          'http://korben.info/rss',
           'http://www.lemonde.fr/videos/rss_full.xml'];
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
                preg_match("/(\<img).*((\/\>)|(\<\/img))/",$feed->items[$i]->content,$result);
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
     * @Route("/Formulaire_enregistrement_RSS")
     * @Template()
     */
    public function Formulaire_enregistrement_RSSAction(Request $request)
    {
        if ($request->getMethod() == 'POST') 
        {
			$URL_SITE_NAME = $_POST["URL_VALEUR_NAME"];
			$URL_SITE_RSS = $_POST["URL_VALEUR_RSS"];
			$vari="Fichier_Sauvegarde_Lien_RSS/Fichier_Lien_RSS.xml";
			$xml= simplexml_load_file($vari);
			$json = json_encode($xml);
			$array = json_decode($json,TRUE);
			array_push($array["id"],(count($array["id"])));
			array_push($array["site"],$URL_SITE_NAME);
			array_push($array["rss"],$URL_SITE_RSS);
			
			$_xml ="<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\r\n";
			$_xml .="<site_web>\r\n";
			
			for ($i = 0; $i < count($array["id"]); $i++)
			{
				$_xml .="	<id>".$array["id"][$i]."</id>\r\n";
				$_xml .="	<site>".$array["site"][$i]."</site>\r\n";
				$_xml .="	<rss>".$array["rss"][$i]."</rss>\r\n\n";
			
			}
			
			$_xml .="</site_web>\r\n";
			
			$file= fopen($vari, "w");
			fwrite($file,$_xml);
			fclose($file);
			
            return array('fichier_xml' => $array);          
        }
        else
        {
			$vari="Fichier_Sauvegarde_Lien_RSS/Fichier_Lien_RSS.xml";
			$xml= simplexml_load_file($vari);
			$json = json_encode($xml);
			$array = json_decode($json,TRUE);
			
            return array('fichier_xml' => $array); 
		}
    }


    /**
     * @Route("/test_style")
     * @Template()
     */
    public function testAction()
    {
        return array();
    }
}
