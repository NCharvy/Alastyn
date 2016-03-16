<?php

namespace Alastyn\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PicoFeed\Reader\Reader;
use Alastyn\AdminBundle\Entity\Suggestion;
use Alastyn\AdminBundle\Form\SuggestionType;
use Symfony\Component\HttpFoundation\Request;

class FrontController extends Controller
{
    /**
     * @Route("/", name = "_index")
     * @Template()
     */
	public function indexAction(Request $req)
    {
        $reader = new Reader;
        $resources = ['http://feeds.howtogeek.com/howtogeek',
          'http://www.lemonde.fr/videos/rss_full.xml',
          'http://www.begeek.fr/feed',
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
                  /*  
                    if ( mettre la bonne image correspondand au flux) {
                      $feed->items[$i]->preimage=
                    }
                  */
                    $feed->items[$i]->preimage=
                    '<img width="100%!important;" class="img-responsive" 
                    src="http://www.allvectors.com/wp-content/uploads/2012/06/abstract-white-background.jpg" />';
                }
                else{
                    $feed->items[$i]->preimage=
                    '<img width="100%!important;" class="img-responsive" 
                    src="http://www.allvectors.com/wp-content/uploads/2012/06/abstract-white-background.jpg" />';
                }
            }

            $feeds[] = $feed;
        }


          $em = $this->getDoctrine()->getManager();
          $Suggestion = new Suggestion;
          $form = $this->get('form.factory')->create(SuggestionType::class, $Suggestion);

          if($form->handleRequest($req)->isValid()){
              $em->persist($Suggestion);
              $em->flush();

              $req->getSession()->getFlashBag()->add('notice', 'La Suggestion a bien été evoyée.');

              return $this->redirectToRoute('_index');
          }
          return array('feeds' => $feeds,'form' => $form->createView());    
        }
        
}
