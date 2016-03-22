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
use Alastyn\AdminBundle\Entity\Pagination;

class FrontController extends Controller
{
    /**
     * @Route("/{page}", name = "_index", defaults={"page": 1})
     * @Template()
     */
	public function indexAction(Request $req, $page)
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
                    'width="100%!important;" src',
                    $feed->items[$i]->preimage);
                }
                else{
                    $feed->items[$i]->preimage=
                    '<img class="img-responsive img-article" 
                    src="bundles/front/img/verre3.jpg" />';
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

        $feeds = array_values($tmp_feeds);



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

        $reg = new Pagination($feeds);
        $reg->setPage($page);
        $pagination = array(
            'page' => $page,
            'route' => '_index',
            'pages_count' => ceil(count($feeds) / $reg->getMaxPerPage()),
            'route_params' => array()
        );
        $feeds = $reg->getList();

          return array(
              'feeds' => $feeds,
              'form' => $form->createView(),
              'pays' => $pays,
              'pagination' => $pagination
          );
        }
        
}
