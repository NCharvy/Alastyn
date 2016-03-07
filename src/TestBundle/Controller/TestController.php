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
}
