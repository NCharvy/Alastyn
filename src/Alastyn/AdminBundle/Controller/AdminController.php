<?php

namespace Alastyn\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PicoFeed\Reader\Reader;
use Symfony\Component\HttpFoundation\Request;

use Alastyn\AdminBundle\Entity\Pays;
use Alastyn\AdminBundle\Entity\Region; 
use Alastyn\AdminBundle\Entity\Domaine;
use Alastyn\AdminBundle\Entity\Flux;
use Alastyn\AdminBundle\Entity\Appellation;
use Alastyn\AdminBundle\Entity\Pagination;

use Alastyn\AdminBundle\Form\PaysType;
use Alastyn\AdminBundle\Form\DomaineType; 
use Alastyn\AdminBundle\Form\FluxType;
use Alastyn\AdminBundle\Form\RegionType;
use Alastyn\AdminBundle\Form\AppellationType;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name = "_indexAdmin")
     * @Template()
     */
    public function indexAction(Request $request)
    {
   //  	$reader = new Reader;
   //      $file=file_get_contents('saved_rss/listeLiens.json');
   //      $datas=json_decode($file);

   //      if ($request->getMethod() == 'POST')
   //      {
   //          $name = $request->request->get('name');
   //          $rss = $request->request->get('rss_link');
            
   //          $datas[] = array('id'=>(count($datas)),'site'=>$name,'rss'=>$rss); 
   //          $textResponse = json_encode($datas,JSON_PRETTY_PRINT);
   //          file_put_contents('saved_rss/listeLiens.json', $textResponse);

   //          $file=file_get_contents('saved_rss/listeLiens.json');
   //          $datas=json_decode($file);
   //      }

   //      $resources = [];
   //      foreach ($datas as $data) {
   //          $resources[] = $data->rss;
   //      }
   //      $feeds = [];

   //      foreach ($resources as $rss) 
   //      {
   //          $Verfification_rss = $this->get('gbprod.my_service')->checkRss($rss);
   //          if($Verfification_rss == "FLUX RSS VALIDER")
   //          {
			// 	$resource = $reader->download($rss);
				
			// 	$parser = $reader->getParser(
			// 		$resource->getUrl(),
			// 		$resource->getContent(),
			// 		$resource->getEncoding()
			// 	);
				
			// 	$feed = $parser->execute();

			// 	$result = [];
			// 	for($i = 0;$i<count($feed->items);$i++) {
			// 		preg_match('/(\<img).*((\/\>)|(\<\/img))/',$feed->items[$i]->content,$result);
			// 		if (count($result) > 0) {
			// 			$feed->items[$i]->preimage=$result[0];
			// 		}else{
			// 			$feed->items[$i]->preimage="";
			// 		}
			// 	}
			// 	$feeds[] = $feed;
			// 	return array('feeds' => $feeds);
			// } 
   //          else
   //          {
   //              return array('feeds' => $Verfification_rss);
   //          }          
   //      }
    }

    /**
     * @Route("/admin/state/create", name="_create_state")
     * @Template("AlastynAdminBundle:Pays:createState.html.twig")
     */
    public function createStateAction(Request $req){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $state = new Pays;
        $form = $this->get('form.factory')->create(PaysType::class, $state);

        if($form->handleRequest($req)->isValid()){
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $state->getIcon();
            $fileName = $file->getClientOriginalName();

            $iconsDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/icons';
            $file->move($iconsDir, $fileName);

            $state->setIcon($fileName);
            $em->persist($state);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le pays a bien été ajouté.');

            return $this->redirectToRoute('_create_state');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/state/update/{id}", name="_update_state")
     * @Template("AlastynAdminBundle:Pays:createState.html.twig")
     */
    public function updateStateAction(Request $req, $id){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $state = $em->getRepository('AlastynAdminBundle:Pays')->find($id);
        $form = $this->get('form.factory')->create(PaysType::class, $state);

        if($form->handleRequest($req)->isValid()){
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $state->getIcon();
            $fileName = $file->getClientOriginalName();

            $iconsDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/icons';
            $file->move($iconsDir, $fileName);

            $state->setIcon($fileName);
            $em->persist($state);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le pays a bien été ajouté.');

            return $this->redirectToRoute('_view_states', array('page' => 1));
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/state/delete/{id}", name="_delete_state")
     * @Template("AlastynAdminBundle:Pays:deleteState.html.twig")
     */
    public function deleteStateAction(Request $req, $id){
        $em = $this->getDoctrine()->getManager();
        $state = $em->getRepository('AlastynAdminBundle:Pays')->find($id);

        $regions = $state->getRegions();
        foreach ($regions as $r) {
            $region = $em->getRepository('AlastynAdminBundle:Region')->find($r->getId());
            $region->setPays();
            $region->setPublication(false);
            $em->persist($region);
        }

        $em->remove($state);
        $em->flush();

        return $this->redirectToRoute('_view_states', array('page' => 1));
    }

    /**
     * @Route("/admin/states/view/{page}", name="_view_states", defaults={"page", 1})
     * @Template("AlastynAdminBundle:Pays:viewStates.html.twig")
     */
    public function viewStateAction($page){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }
        $em = $this->getDoctrine()->getManager();
        $pays = $em->createQueryBuilder()
            ->select('pays')
            ->from('AlastynAdminBundle:Pays','pays');
        $domaines = new Pagination($pays);
        $domaines->setPage($page);
        $domains_count = $em->getRepository('AlastynAdminBundle:Pays')
            ->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->getQuery()
            ->getSingleScalarResult();
        $pagination = array(
            'page' => $page,
            'route' => '_view_states',
            'pages_count' => ceil($domains_count / $domaines->getMaxPerPage()),
            'route_params' => array()
        );
        $states = $domaines->getList();

        return array(
            'states' => $states,
            'pagination' => $pagination
        );
    }

    /**
     * @Route("/admin/region/create", name="_create_region")
     * @Template("AlastynAdminBundle:Region:createRegion.html.twig")
     */
    public function createRegionAction(Request $req){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $region = new Region;
        $form = $this->get('form.factory')->create(RegionType::class, $region);

        if($form->handleRequest($req)->isValid()){
            $em->persist($region);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'La région a bien été ajoutée.');

            return $this->redirectToRoute('_create_region');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/region/update/{id}", name="_update_region")
     * @Template("AlastynAdminBundle:Region:createRegion.html.twig")
     */
    public function updateRegionAction(Request $req, $id){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $region = $em->getRepository('AlastynAdminBundle:Region')->find($id);
        $form = $this->get('form.factory')->create(RegionType::class, $region);

        if($form->handleRequest($req)->isValid()){
            $em->persist($region);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'La région a bien été ajoutée.');

            return $this->redirectToRoute('_view_regions', array('page' => 1));
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/regions/view/{page}", name="_view_regions", defaults={"page", 1})
     * @Template("AlastynAdminBundle:Region:viewRegions.html.twig")
     */
    public function viewRegionAction($page){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }
        $em = $this->getDoctrine()->getManager();
        $region = $em->createQueryBuilder()
            ->select('region')
            ->from('AlastynAdminBundle:Region','region');
        $reg = new Pagination($region);
        $reg->setPage($page);
        $regions_count = $em->getRepository('AlastynAdminBundle:Region')
            ->createQueryBuilder('r')
            ->select('COUNT(r)')
            ->getQuery()
            ->getSingleScalarResult();
        $pagination = array(
            'page' => $page,
            'route' => '_view_regions',
            'pages_count' => ceil($regions_count / $reg->getMaxPerPage()),
            'route_params' => array()
        );
        $regions = $reg->getList();

        return array(
            'regions' => $regions,
            'pagination' => $pagination
        );
    }

    /**
     * @Route("/admin/region/delete/{id}", name="_delete_region")
     */
    public function deleteRegionAction(Request $req, $id){
        $em = $this->getDoctrine()->getManager();
        $region = $em->getRepository('AlastynAdminBundle:Region')->find($id);

        $domains = $region->getDomaines();
        foreach ($domains as $d) {
            $domain = $em->getRepository('AlastynAdminBundle:Domaine')->find($d->getId());
            $domain->setRegion();
            $domain->setPublication(false);
            $em->persist($domain);
        }

        $wines = $region->getAppellations();
        foreach ($wines as $w) {
            $wine = $em->getRepository('AlastynAdminBundle:Appellation')->find($w->getId());
            $wine->setRegion();
            $wine->setPublication(false);
            $em->persist($wine);
        }

        $em->remove($region);
        $em->flush();

        return $this->redirectToRoute('_view_regions', array('page' => 1));
    }

    /**
     * @Route("/admin/domain/create", name="_create_domain")
     * @Template("AlastynAdminBundle:Domaine:createDomain.html.twig")
     */
    public function createDomainAction(Request $req){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $domain = new Domaine;
        $form = $this->get('form.factory')->create(DomaineType::class, $domain);

        if($form->handleRequest($req)->isValid()){
            $em->persist($domain);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le domaine a bien été ajouté.');

            return $this->redirectToRoute('_create_domain');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/domain/list/{page}", name="_list_domain", defaults={"page", 1})
     * @Template("AlastynAdminBundle:Domaine:listDomain.html.twig")
     */
    public function listDomainAction($page){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }
        $em = $this->getDoctrine()->getManager();
        $domaine = $em->createQueryBuilder()
            ->select('domaine')
            ->from('AlastynAdminBundle:domaine','domaine');
        $domaines = new Pagination($domaine);
        $domaines->setPage($page);
        $domains_count = $em->getRepository('AlastynAdminBundle:Domaine')
            ->createQueryBuilder('d')
            ->select('COUNT(d)')
            ->getQuery()
            ->getSingleScalarResult();
        $pagination = array(
            'page' => $page,
            'route' => '_list_domain',
            'pages_count' => ceil($domains_count / $domaines->getMaxPerPage()),
            'route_params' => array()
        );
        $domains = $domaines->getList();

        return array(
            'domains' => $domains,
            'pagination' => $pagination
        );
    }

    /**
     * @Route("/admin/domain/update/{id}", name="_update_domain")
     * @Template("AlastynAdminBundle:Domaine:createDomain.html.twig")
     */
    public function updateDomainAction(Request $req, $id){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $domain = $em->getRepository('AlastynAdminBundle:Domaine')->find($id);
        $form = $this->get('form.factory')->create(DomaineType::class, $domain);

        if($form->handleRequest($req)->isValid()){
            $em->persist($domain);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le domaine a bien été ajouté.');

            return $this->redirectToRoute('_list_domain', array('page' => 1));
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/domain/delete/{id}", name="_delete_domain")
     */
    public function deleteDomainAction(Request $req, $id){
        $em = $this->getDoctrine()->getManager();
        $domain = $em->getRepository('AlastynAdminBundle:Domaine')->find($id);

        $flows = $domain->getFlux();
        foreach ($flows as $f) {
            $flow = $em->getRepository('AlastynAdminBundle:Flux')->find($f->getId());
            $flow->setDomaine();
            $flow->setStatut('Absence de liaison');
            $flow->setPublication(false);
            $em->persist($flow);
        }

        $em->remove($domain);
        $em->flush();

        return $this->redirectToRoute('_list_domain', array('page' => 1));
    }

    /**
     * @Route("/admin/flow/create", name="_create_flow")
     * @Template("AlastynAdminBundle:Flux:createFlow.html.twig")
     */
    public function createFlowAction(Request $req){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $flow = new Flux;
        $form = $this->get('form.factory')->create(FluxType::class, $flow);

        if($form->handleRequest($req)->isValid()){
            $check_rss = $this->get('gbprod.my_service')->checkRss($flow->getUrl());
            $flow->setStatut($check_rss);
            if($check_rss != 'Valide') {
                $flow->setPublication(false);
            }
            $em->persist($flow);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le flux RSS a bien été ajouté.');

            return $this->redirectToRoute('_create_flow');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/flow/list/{page}", name="_list_flow", defaults={"page", 1})
     * @Template("AlastynAdminBundle:Flux:listFlow.html.twig")
     */
    public function listFlowAction($page){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }
        $em = $this->getDoctrine()->getManager();
        $flux = $em->createQueryBuilder()
            ->select('flux')
            ->from('AlastynAdminBundle:Flux','flux');
        $fl = new Pagination($flux);
        $fl->setPage($page);
        $flows_count = $em->getRepository('AlastynAdminBundle:Flux')
            ->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->getQuery()
            ->getSingleScalarResult();
        $pagination = array(
            'page' => $page,
            'route' => '_list_flow',
            'pages_count' => ceil($flows_count / $fl->getMaxPerPage()),
            'route_params' => array()
        );
        $flows = $fl->getList();

        return array(
            'flows' => $flows,
            'pagination' => $pagination
        );
    }

    /**
     * @Route("/admin/flow/update/{id}", name="_update_flow")
     * @Template("AlastynAdminBundle:Flux:createFlow.html.twig")
     */
    public function updateFlowAction(Request $req, $id){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $flow = $em->getRepository('AlastynAdminBundle:Flux')->find($id);
        $form = $this->get('form.factory')->create(FluxType::class, $flow);

        if($form->handleRequest($req)->isValid()){
            $check_rss = $this->get('gbprod.my_service')->checkRss($flow->getUrl());
            $flow->setStatut($check_rss);
            if($check_rss != 'Valide') {
                $flow->setPublication(false);
            }
            $em->persist($flow);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le flux a bien été ajouté.');

            return $this->redirectToRoute('_list_flow', array('page' => 1));
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/flow/delete/{id}", name="_delete_flow")
     */
    public function deleteFlowAction(Request $req, $id){
        $em = $this->getDoctrine()->getManager();
        $domain = $em->getRepository('AlastynAdminBundle:Flux')->find($id);

        $em->remove($domain);
        $em->flush();

        return $this->redirectToRoute('_list_flow', array('page' => 1));
    }

    /**
     * @Route("/admin/wine/create", name="_create_wine")
     * @Template("AlastynAdminBundle:Appellation:createWine.html.twig")
     */
    public function createWineAction(Request $req){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $wine = new Appellation;
        $form = $this->get('form.factory')->create(AppellationType::class, $wine);

        if($form->handleRequest($req)->isValid()){
            $em->persist($wine);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'L\'appellation a bien été ajoutée.');

            return $this->redirectToRoute('_create_wine');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/wine/list", name="_list_wine")
     * @Template("AlastynAdminBundle:Appellation:listWine.html.twig")
     */
    public function listWineAction(){
        $wines = $this->getDoctrine()
            ->getRepository('AlastynAdminBundle:Appellation')
            ->findAll()
        ;

        return array('wines' => $wines);
    }

    /**
     * @Route("/admin/wine/update/{id}", name="_update_wine")
     * @Template("AlastynAdminBundle:Appellation:createWine.html.twig")
     */
    public function updateWineAction(Request $req, $id){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $wine = $em->getRepository('AlastynAdminBundle:Appellation')->find($id);
        $form = $this->get('form.factory')->create(AppellationType::class, $wine);

        if($form->handleRequest($req)->isValid()){
            $em->persist($wine);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'L\'appellation a bien été ajoutée.');

            return $this->redirectToRoute('_list_wine', array('page' => 1));
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/wine/delete/{id}", name="_delete_wine")
     */
    public function deleteWineAction(Request $req, $id){
        $em = $this->getDoctrine()->getManager();
        $wine = $em->getRepository('AlastynAdminBundle:Appellation')->find($id);

        $em->remove($wine);
        $em->flush();

        return $this->redirectToRoute('_list_wine', array('page' => 1));
    }

    /**
     * @Route("/admin/suggestions/view/{page}", name="_view_suggests", defaults={"page", 1})
     * @Template("AlastynAdminBundle:Suggestion:viewSuggestions.html.twig")
     */
    public function viewSuggestionAction($page){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }
        $em = $this->getDoctrine()->getManager();
        $suggest = $em->createQueryBuilder()
            ->select('suggestion')
            ->from('AlastynAdminBundle:Suggestion','suggestion');
        $sg = new Pagination($suggest);
        $sg->setPage($page);
        $suggest_count = $em->getRepository('AlastynAdminBundle:Suggestion')
            ->createQueryBuilder('s')
            ->select('COUNT(s)')
            ->getQuery()
            ->getSingleScalarResult();
        $pagination = array(
            'page' => $page,
            'route' => '_view_suggests',
            'pages_count' => ceil($suggest_count / $sg->getMaxPerPage()),
            'route_params' => array()
        );
        $suggestions = $sg->getList();

        return array(
            'suggestions' => $suggestions,
            'pagination' => $pagination
        );
    }
}
