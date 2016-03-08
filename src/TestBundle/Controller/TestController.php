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
        $resources = ['https://news.ycombinator.com/rss', 'http://unodieuxconnard.com/feed/'];
        $feeds = [];

        foreach ($resources as $rss) {
            $resource = $reader->download($rss);
            $parser = $reader->getParser(
                $resource->getUrl(),
                $resource->getContent(),
                $resource->getEncoding()
            );

            $feeds[] = $parser->execute();
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
			$URL_SITE_RSS = $_POST["URL_VALEUR"];
			$vari="../Fichier_Sauvegarde_Lien_RSS/Fichier_Lien_RSS.xml";
			$xml= simplexml_load_file($vari);
			$json = json_encode($xml);
			$array = json_decode($json,TRUE);
			
            return array('feed' => $URL_SITE_RSS, 'fichier_xml' => $array);          
        }
        else
        {
			return array('feed' => "Pas de donnée", 'fichier_xml' => "pas de donné");
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
