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
        $flows_count = $em->getRepository('AlastynAdminBundle:Flux')
            ->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->where('f.publication = true')
            ->getQuery()
            ->getSingleScalarResult();
        $flow = new Pagination($query, $page, 10);
        $resources = $query->setFirstResult(($flow->getPage()-1) * $flow->getMaxPerPage())
            ->setMaxResults($flow->getMaxPerPage())->getResult();
        $feeds = [];
        $tmp_feeds = [];

        if (!$resources) {
            throw $this->createNotFoundException('No RSS feeds found ! ');
        }

        foreach ($resources as $rss) {
          echo $rss->getUrl().'******';
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
            echo $feed->items[0].'++++++++++++';

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
     * @Route("/region/{idr}", name = "_region_index")
     * @Template("AlastynFrontBundle:Front:index.html.twig")
     */
    public function regionAction(Request $req, $idr) {
        $reader = new Reader;
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT f,d,r FROM AlastynAdminBundle:Flux f inner JOIN f.domaine d inner JOIN d.region r
          WHERE f.publication = true AND r.id = $idr");
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
                        '<img class="img-responsive img-article_une"
                    src="/bundles/front/img/verre3.jpg" />';
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

        return array(
            'feeds' => $feeds,
            'form' => $form->createView(),
            'states' => $pays
        );
    }

    /**
     * @Route("/post/mail", name="_post_mail")
     */
    public function sendMailAction(Request $req){
        $prenom = $req->request->get('prenom');
        $nom = $req->request->get('nom');
        $domaine = $req->request->get('domaine');
        $email = $req->request->get('email');
        $message = $req->request->get('message');

        $sendMail = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom('nathan.charvy@gmail.com')
            ->setTo($email)
            ->setBody(("Test $prenom $nom du domaine $domaine"), 'text/html');

         $this->get('mailer')->send($sendMail);

        return $this->redirectToRoute('_index');
    }

    /**
    * @Route("/api/recherche_region_json")
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

    /**
     * @Route("/api/recherche_good_region_json")
     */
    public function rechercheGoodJsonAction(Request $request)
    {
        if($request->getMethod() == 'POST')
        {
            $id = json_decode($request->getContent());
            $em = $this->getDoctrine()->getManager();
            $region = [];
            $wines = [];
            $Regions = $em->getRepository('AlastynAdminBundle:Region')->findByPays($id->idpays);
            foreach ($Regions as $r)
            {
                $nb_flux = $em->getRepository('AlastynAdminBundle:Flux')
                              ->createQueryBuilder('f')
                              ->select('COUNT(f)')
                              ->innerJoin('f.domaine', 'd')
                              ->innerJoin('d.region', 'r')
                              ->where('r.id = ' . $r->getId())
                              ->getQuery()
                              ->getSingleScalarResult();
                if($nb_flux > 0){
                    $region[] = array($r->getNom(), $nb_flux);
                }

                $nb_wines = $em->getRepository('AlastynAdminBundle:Appellation')
                               ->createQueryBuilder('a')
                               ->select('COUNT(a)')
                               ->innerJoin('a.region', 'r')
                               ->where('r.id = ' . $r->getId())
                               ->getQuery()
                               ->getSingleScalarResult();
                if($nb_wines > 0){
                    $wines[] = array($r->getNom(), $nb_wines);
                }
            }
        }
        return new response(json_encode(array("data" => $region, "wines" => $wines)));
    }
}
