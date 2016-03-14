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

use Alastyn\AdminBundle\Form\PaysType;
use Alastyn\AdminBundle\Form\DomaineType;
use Alastyn\AdminBundle\Form\FluxType;
use Alastyn\AdminBundle\Form\RegionType;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name = "_indexAdmin")
     * @Template()
     */
    public function indexAction(Request $request)
    {
    	$reader = new Reader;
        $file=file_get_contents('saved_rss/listeLiens.json');
        $datas=json_decode($file);

        if ($request->getMethod() == 'POST')
        {
            $name = $request->request->get('name');
            $rss = $request->request->get('rss_link');
            
            $datas[] = array('id'=>(count($datas)),'site'=>$name,'rss'=>$rss); 
            $textResponse = json_encode($datas,JSON_PRETTY_PRINT);
            file_put_contents('saved_rss/listeLiens.json', $textResponse);

            $file=file_get_contents('saved_rss/listeLiens.json');
            $datas=json_decode($file);
        }

        $resources = [];
        foreach ($datas as $data) {
            $resources[] = $data->rss;
        }
        $feeds = [];

        foreach ($resources as $rss) 
        {
			try
			{
				$resource = $reader->download($rss);
				
				$parser = $reader->getParser(
					$resource->getUrl(),
					$resource->getContent(),
					$resource->getEncoding()
				);
				
				$feed = $parser->execute();

				$result = [];
				for($i = 0;$i<count($feed->items);$i++) {
					preg_match('/(\<img).*((\/\>)|(\<\/img))/',$feed->items[$i]->content,$result);
					if (count($result) > 0) {
						$feed->items[$i]->preimage=$result[0];
					}else{
						$feed->items[$i]->preimage="";
					}
				}
				$feeds[] = $feed;
				return array('feeds' => $feeds);
			}
			catch(Exception $e) 
			{
				return array('feeds' => $e);
			}
            
        }
    }

    /**
     * @Route("/add_rss")
     * @Template()
     */
    public function addRssAction(Request $request)
    {

        $contentFile=file_get_contents("saved_rss/listeLiens.json");
        $array=json_decode($contentFile);

        if ($request->getMethod() == 'POST') 
        {
            $URL_SITE_NAME = $_POST["URL_VALEUR_NAME"];
            $URL_SITE_RSS = $_POST["URL_VALEUR_RSS"];
            $Verfification_rss = $this->get('gbprod.my_service')->Service_verification_rss($URL_SITE_RSS);

       		if($Verfification_rss == "FLUX RSS VALIDER")
       		{

				array_push($array,array("id"=>(count($array)),"site"=>$URL_SITE_NAME,"rss"=>$URL_SITE_RSS)); 
				$textResponse = json_encode($array,JSON_PRETTY_PRINT);
				file_put_contents("saved_rss/listeLiens.json", $textResponse);

       		}
		}
		else
		{
			$Verfification_rss = "pas de donnée";
		}
        return array('file' => $array, 'Verif_Exist' => $Verfification_rss);    
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

            return $this->redirectToRoute('_indexAdmin');
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

        $em->remove($state);
        $em->flush();

        return $this->redirectToRoute('_view_states');
    }

    /**
     * @Route("/admin/states/view", name="_view_states")
     * @Template("AlastynAdminBundle:Pays:viewStates.html.twig")
     */
    public function viewStateAction(Request $req){
        $em = $this->getDoctrine()->getManager();
        $states = $em->getRepository('AlastynAdminBundle:Pays')->findAll();
        return array('states' => $states);
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

            return $this->redirectToRoute('_indexAdmin');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/regions/view", name="_view_regions")
     * @Template("AlastynAdminBundle:Region:viewRegions.html.twig")
     */
    public function viewRegionAction(Request $req){
        $em = $this->getDoctrine()->getManager();
        $regions = $em->getRepository('AlastynAdminBundle:Region')->findAll();
        return array('regions' => $regions);
    }

    /**
     * @Route("/admin/region/delete/{id}", name="_delete_region")
     */
    public function deleteRegionAction(Request $req, $id){
        $em = $this->getDoctrine()->getManager();
        $region = $em->getRepository('AlastynAdminBundle:Region')->find($id);

        $em->remove($region);
        $em->flush();

        return $this->redirectToRoute('_view_regions');
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
     * @Route("/admin/domain/list", name="_list_domain")
     * @Template("AlastynAdminBundle:Domaine:listDomain.html.twig")
     */
    public function listDomainAction(){
        $domains = $this->getDoctrine()
            ->getRepository('AlastynAdminBundle:Domaine')
            ->findAll()
        ;

        return array('domains' => $domains);
    }

    /**
     * @Route("/admin/domain/update/{id}", name="_update_domain")
     * @Template("AlastynAdminBundle:Region:createDomain.html.twig")
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

            return $this->redirectToRoute('_indexAdmin');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/domain/delete/{id}", name="_delete_domain")
     */
    public function deleteDomainAction(Request $req, $id){
        $em = $this->getDoctrine()->getManager();
        $domain = $em->getRepository('AlastynAdminBundle:Domaine')->find($id);

        $em->remove($domain);
        $em->flush();

        return $this->redirectToRoute('_list_domain');
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
            # Faire la vérification RSS, ajouter status + passer publication à false si erreur
            $flow->setStatus('Valide');
            $em->persist($flow);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le flux RSS a bien été ajouté.');

            return $this->redirectToRoute('_create_flow');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/flow/list", name="_list_flow")
     * @Template("AlastynAdminBundle:Flux:listFlow.html.twig")
     */
    public function listFlowAction(){
        $flows = $this->getDoctrine()
            ->getRepository('AlastynAdminBundle:Flux')
            ->findAll()
        ;

        return array('flows' => $flows);
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
            $em->persist($flow);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le flux a bien été ajouté.');

            return $this->redirectToRoute('_indexAdmin');
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

        return $this->redirectToRoute('_list_flow');
    }
}
