<?php

namespace TestBundle\Controller;

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
        //$resources = ['https://news.ycombinator.com/rss', 'http://unodieuxconnard.com/feed/'];
        // $resource = $reader->download('https://news.ycombinator.com/rss');
        $resource = $reader->download('http://korben.info/rss');
        //$feeds = [];

        /*foreach ($resources as $resource) {
            $parser = $reader->getParser(
                $resource->getUrl(),
                $resource->getContent(),
                $resource->getEncoding()
            );

            $feeds += $parser->execute();
        }*/
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

        return array('feed' => $feed);
    }


    /**
     * @Route("/Formulaire_enregistrement_RSS")
     * @Template()
     */
    public function Formulaire_enregistrement_RSSAction()
    {
        $reader = new Reader;
        // $resource = $reader->download('https://news.ycombinator.com/rss');
        $resource = $reader->download('http://unodieuxconnard.com/feed/');

        $parser = $reader->getParser(
            $resource->getUrl(),
            $resource->getContent(),
            $resource->getEncoding()
        );

        $feed = $parser->execute();

        return array('feed' => $feed);
    }
    

    /**
     * @Route("/enregistrement_RSS")
     * @Template()
     */
    public function enregistrement_RSSAction()
    {
        $reader = new Reader;
        // $resource = $reader->download('https://news.ycombinator.com/rss');
        $resource = $reader->download('http://unodieuxconnard.com/feed/');

        $parser = $reader->getParser(
            $resource->getUrl(),
            $resource->getContent(),
            $resource->getEncoding()
        );

        $feed = $parser->execute();

        return array('feed' => $feed);
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
