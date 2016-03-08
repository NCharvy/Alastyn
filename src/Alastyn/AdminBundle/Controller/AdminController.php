<?php

namespace Alastyn\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PicoFeed\Reader\Reader;

class AdminController extends Controller
{
    /**
     * @Route("/admin")
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

}
