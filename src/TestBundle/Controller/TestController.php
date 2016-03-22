<?php

namespace TestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PicoFeed\Reader\Reader;
use Alastyn\AdminBundle\Entity;
use PicoFeed\Client\Url;



class TestController extends Controller
{
  /**
  * @Route("/recherche")
  * @Template()
  */
  public function rechercheAction()
  {
    $em = $this->getDoctrine()->getManager();
    $pays = $em->getRepository('AlastynAdminBundle:Pays')->findAll();
    return array("states" => $pays);
  }

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
    * @Route("/checkAllFeeds")
    * @Template("TestBundle:Test:checkAllFeeds.html.twig")
    */
    public function checkAllFeedsAction() {
        $em = $this->getDoctrine()->getManager();

        $flows = $em->createQueryBuilder()
            ->select('flux')
            ->from('AlastynAdminBundle:Flux','flux')
            ->getQuery()
            ->getResult();

        // foreach ($flows as $flow) {
        for ($i=20; $i < 50; $i++) {
            $flow = $flows[$i];
            $check_rss = $this->get('check_rss')->checkRss($flow->getUrl());
            $flow->setStatut($check_rss);
            if($check_rss != 'Valide' || !$flow->getDomaine()->getPublication()) {
                $flow->setPublication(false);
            }
            $em->persist($flow);
            echo '<p>'.$flow->getStatut().'</p>';
        }
        $em->flush();

        $test = 'done';

        return array('test' => $test);
    }

  /**
  * @Route("/flux_rss")
  */
  public function flux_rssAction()
  {
    $wikipediaURL = 'https://champagnecharlierbilliard.com/category/actualites-de-la-maison/feed/';

        // create curl resource
        $ch = \curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $wikipediaURL);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $r = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);   


   // $r = \http_get('http://www.chaigne.fr/blog/feed');

/*
$aContext = array(
    'http' => array(
        'proxy' => 'proxy:8080',
        'request_fulluri' => true,
    ),
);
$cxContext = stream_context_create($aContext);

$r = var_dump(file_get_contents("http://moulin-garreau.over-blog.com/rss", False, $cxContext));
*/
/*$url = \Purl\Url::parse('moulin-garreau.over-blog.com/rss')
    ->set('Host', 'moulin-garreau.over-blog.com')
    ->set('User-Agent', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:45.0) Gecko/20100101 Firefox/45.0')
    ->set('Accept-language', 'en-US,en;q=0.5')
    ->set('Connection', "keep-alive");

  //$r = file_get_contents(Request::create("http://moulin-garreau.over-blog.com/rss"));
    $r = $GET_[Request::create("http://moulin-garreau.over-blog.com/rss")];
*/
    $url = 'https://champagnecharlierbilliard.com/category/actualites-de-la-maison/feed/';
    $r = var_dump($r);


    return new response(json_encode(array("data" => $r)));
  }
}
