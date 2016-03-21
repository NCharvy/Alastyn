<?php

namespace Alastyn\FrontBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT f FROM AlastynAdminBundle:Flux f WHERE f.publication = true');
        $resources = $query->getResult();
        $feeds = [];
        $tmp_feeds = [];

        if (!$resources) {
            throw $this->createNotFoundException('No RSS feeds found ! ');
        }

        foreach ($resources as $rss) {
          try {
            $resource = $reader->download($rss->getUrl());
          } catch(Exception $e) {
            echo 'Exception reçue : ', $e->getMessage(), "\n";
          }

            $parser = $reader->getParser(
                $resource->getUrl(),
                $resource->getContent(),
                $resource->getEncoding()
            );

            $feed = $parser->execute();

            $result = [];
            $keydate ="";
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



              $var= json_encode($feed->items[$i]->date);
              $test_date = json_decode($var)->date;

              if($test_date > $keydate){
                $keydate = $test_date;
              }

            }

            
            $tmp_feeds[$keydate] = $feed;
        }

        krsort($tmp_feeds);

        foreach ($tmp_feeds as $key => $value) {
          $feeds[] = $value;
        }


        $pays = $em->getRepository('AlastynAdminBundle:Pays')->findByPublication(true);

          $em = $this->getDoctrine()->getManager();
          $suggestion = new Suggestion;
          $form = $this->get('form.factory')->create(SuggestionType::class, $suggestion);

          if($form->handleRequest($req)->isValid()){
              $em->persist($suggestion);
              $em->flush();

              $req->getSession()->getFlashBag()->add('notice', 'La Suggestion a bien été evoyée.');

              return $this->redirectToRoute('_index');
          }
          return array('feeds' => $feeds, 'form' => $form->createView(), 'pays' => $pays);    
        }
        
}
