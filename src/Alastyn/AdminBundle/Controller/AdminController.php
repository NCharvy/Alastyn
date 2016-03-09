<?php

namespace Alastyn\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PicoFeed\Reader\Reader;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name = "_indexAdmin")
     * @Template()
     */
    public function indexAction()
    {
    	$reader = new Reader;
        $resources = ['http://unodieuxconnard.com/feed/', 'http://korben.info/rss'];
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

}
