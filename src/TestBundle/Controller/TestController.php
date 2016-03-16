<?php

namespace TestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PicoFeed\Reader\Reader;
use Alastyn\AdminBundle\Entity;

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
