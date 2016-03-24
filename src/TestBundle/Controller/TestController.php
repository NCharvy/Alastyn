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
    * @Route("/test/checkAllFeeds")
    * @Template("TestBundle:Test:checkAllFeeds.html.twig")
    */
    public function checkAllFeedsAction() {
        $em = $this->getDoctrine()->getManager();

        $flows = $em->createQueryBuilder()
            ->select('flux')
            ->from('AlastynAdminBundle:Flux','flux')
            ->getQuery()
            ->getResult();

        // $i = 0;
        // foreach ($flows as $flow) {
        for ($i=1211; $i < 1727; $i++) {
            $flow = $flows[$i];
            $check_rss = $this->get('check_rss')->checkRss($flow->getUrl());
            $flow->setStatut($check_rss);
            if($check_rss == 'Valide' && $flow->getDomaine()->getPublication()) {
                $flow->setPublication(true);
            } else {
                $flow->setPublication(false); 
            }
            $em->persist($flow);
            echo '<p>'.$flow->getStatut().'   '.$i.'</p>';
            // $i++;
        }
        $em->flush();

        $test = 'done';

        return array('test' => $test);
    }

  /**
  * @Route("/test/flux_rss")
  */
  public function flux_rssAction()
  {
    $url = 'http://applewoodwinery.com/index.php?option=com_myblog&amp;Itemid=58&amp;task=rss';

    $check_rss = $this->get('check_rss')->checkRss($url);

    return new response(json_encode(array("data" => $check_rss)));
  }
}
