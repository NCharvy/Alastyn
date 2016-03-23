<?php

namespace Alastyn\FrontBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PicoFeed\Reader\Reader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Alastyn\AdminBundle\Entity\Pagination;
use Alastyn\AdminBundle\Entity\Suggestion;
use Alastyn\AdminBundle\Form\SuggestionType;

class FrontController extends Controller
{
    /**
     * @Route("/{page}", name = "_index", defaults={"page": 1}), requirements={"page": "\d+"}
     * @Template()
     */
	public function indexAction(Request $req, $page) {
        $reader = new Reader;
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT f,d,r FROM AlastynAdminBundle:Flux f inner JOIN f.domaine d inner JOIN d.region r 
          WHERE f.publication = true');
        // $query = $em->createQuery('SELECT f FROM AlastynAdminBundle:Flux f WHERE f.publication = true');
        $flow = new Pagination($query, $page, 10);
        $resources = $query->setFirstResult(($flow->getPage()-1) * $flow->getMaxPerPage())
            ->setMaxResults($flow->getMaxPerPage())->getResult();
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
                    '<img class="img-responsive img-article_une" 
                    src="bundles/front/img/verre3.jpg" />';
                }

              $var= json_encode($feed->items[$i]->date);
              $test_date = json_decode($var)->date;

              if($test_date > $keydate){
                $keydate = $test_date;
              }
              $feed->items[$i]->icone = $rss->getDomaine()->getRegion()->getIcone();
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

        $flows_count = $em->getRepository('AlastynAdminBundle:Flux')
            ->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->where('f.publication = true')
            ->getQuery()
            ->getSingleScalarResult();
        $pagination = array(
            'page' => $flow->getPage(),
            'route' => '_index',
            'pages_count' => ceil($flows_count / $flow->getMaxPerPage()),
            'route_params' => array()
        );

        return array(
            'feeds' => $feeds,
            'form' => $form->createView(),
            'states' => $pays,
            'pagination' => $pagination
        );
    }

    /**
    * @Route("/recherche_region_json")
    */
  public function recherche_region_jsonAction(Request $request)
  {
    if($request->getMethod() == 'POST')
    {
      $id = json_decode($request->getContent());
      $em = $this->getDoctrine()->getManager();
      $Regions = $em->getRepository('AlastynAdminBundle:Region')->findByPays($id[0]->country_id);
      foreach ($Regions as $key => $value) 
      { 
        $Region[0][$key] = $value->getNom();
        $Region[1][$key] = $value->getId();
        $Region[2] = $key;
      }
    }
    return new response(json_encode(array("data" => $Region)));
  }
}
