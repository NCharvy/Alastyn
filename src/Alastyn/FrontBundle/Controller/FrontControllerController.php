<?php

namespace Alastyn\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PicoFeed\Reader\Reader;

class FrontControllerController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
	public function indexAction()
    {
        $reader = new Reader;
        $resources = ['http://feeds.howtogeek.com/howtogeek',
         'http://unodieuxconnard.com/feed/',
          'http://korben.info/rss',
           'http://www.lemonde.fr/videos/rss_full.xml','http://www.begeek.fr/feed',
           'http://feeds2.feedburner.com/LeJournalduGeek',
            'http://www.journaldunet.com/rss/',
             'http://feeds.feedburner.com/fubiz'];
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
                    $feed->items[$i]->preimage = preg_replace("/src/",
                    'width="100%!important;" class="img-responsive" src',
                    $feed->items[$i]->preimage);
                }
                else{
                    $feed->items[$i]->preimage=
                    '<img width="100%!important;" class="img-responsive" src="http://www.allvectors.com/wp-content/uploads/2012/06/abstract-white-background.jpg" />';
                }
            }

            $feeds[] = $feed;
        }
        return array('feeds' => $feeds);
    }


}
