<?php

namespace Alastyn\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PicoFeed\Reader\Reader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;

use Alastyn\AdminBundle\Entity\Pays;
use Alastyn\AdminBundle\Entity\Region; 
use Alastyn\AdminBundle\Entity\Domaine;
use Alastyn\AdminBundle\Entity\Flux;
use Alastyn\AdminBundle\Entity\Appellation;
use Alastyn\AdminBundle\Entity\Pagination;
use Alastyn\AdminBundle\Entity\Suggestion;

use Alastyn\AdminBundle\Form\PaysType;
use Alastyn\AdminBundle\Form\DomaineType; 
use Alastyn\AdminBundle\Form\FluxType;
use Alastyn\AdminBundle\Form\RegionType;
use Alastyn\AdminBundle\Form\AppellationType;
use Alastyn\AdminBundle\Form\SuggestionType;
use Alastyn\AdminBundle\Form\SuggestionCheckType;

class AdminController extends Controller
{
    /**
     * @Route("/admin/index", name = "_indexAdmin")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }
        $stats_flows = [];
        $country_flows = [];
        $country_wines = [];

        $em = $this->getDoctrine()->getManager();

        // Etat des flux
        $stats_flows['valid'] = $em->getRepository('AlastynAdminBundle:Flux')
            ->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->where('f.statut = :statut')
            ->setParameter('statut', 'Valide')
            ->getQuery()
            ->getSingleScalarResult();

        $stats_flows['not_found'] = $em->getRepository('AlastynAdminBundle:Flux')
            ->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->where('f.statut = :statut')
            ->setParameter('statut', 'page introuvable')
            ->getQuery()
            ->getSingleScalarResult();

        $stats_flows['invalid'] = $em->getRepository('AlastynAdminBundle:Flux')
            ->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->where('f.statut = :statut')
            ->setParameter('statut', 'Format du Document invalide ou page introuvable')
            ->getQuery()
            ->getSingleScalarResult();

        $stats_flows['error'] = $em->getRepository('AlastynAdminBundle:Flux')
            ->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->where('f.statut <> :valid AND f.statut <> :not_found AND f.statut <> :invalid')
            ->setParameter('valid', 'Valide')
            ->setParameter('not_found', 'page introuvable')
            ->setParameter('invalid', 'Format du Document invalide ou page introuvable')
            ->getQuery()
            ->getSingleScalarResult();

        // Nombre de flux par pays
        $states = $em->createQueryBuilder()
            ->select('pays')
            ->from('AlastynAdminBundle:Pays','pays')
            ->orderBy('pays.nom', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($states as $state) {
            $country_flows[$state->getNom()] = $em->getRepository('AlastynAdminBundle:Flux')
                ->createQueryBuilder('f')
                ->select('COUNT(f)')
                ->join('f.domaine', 'd')
                ->join('d.region', 'r')
                ->join('r.pays', 'p')
                ->where('p.nom = :pays')
                ->setParameter('pays', $state->getNom())
                ->getQuery()
                ->getSingleScalarResult();

            $country_wines[$state->getNom()] = $em->getRepository('AlastynAdminBundle:Appellation')
                ->createQueryBuilder('a')
                ->select('COUNT(a)')
                ->join('a.region', 'r')
                ->join('r.pays', 'p')
                ->where('p.nom = :pays')
                ->setParameter('pays', $state->getNom())
                ->getQuery()
                ->getSingleScalarResult();
        }

        return array(
            'notif' => $this->getNotif(),
            'states' => $states,
            'stats_flows' => $stats_flows,
            'country_flows' => $country_flows,
            'country_wines' => $country_wines
        );
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
            $file = $state->getIcone();
            $fileName = $file->getClientOriginalName();

            $iconsDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/icons';
            $file->move($iconsDir, $fileName);

            $state->setIcone($fileName);
            $em->persist($state);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le pays a bien été ajouté.');

            return $this->redirectToRoute('_create_state');
        }

        return array('form' => $form->createView(), 'notif' => $this->getNotif());
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
            $file = $state->getIcone();
            $fileName = $file->getClientOriginalName();

            $iconsDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/icons';
            $file->move($iconsDir, $fileName);

            $state->setIcone($fileName);
            $em->persist($state);

            $this->get('check_datas')->checkPublicationRegions($em, $state);

            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le pays a bien été ajouté.');

            return $this->redirectToRoute('_view_states');
        }

        return array('form' => $form->createView(), 'notif' => $this->getNotif());
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
     * @Route("/admin/states/view", name="_view_states")
     * @Template("AlastynAdminBundle:Pays:viewStates.html.twig")
     */
    public function viewStateAction(){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $states = $em->createQueryBuilder()
            ->select('pays')
            ->from('AlastynAdminBundle:Pays','pays')
            ->getQuery()
            ->getResult();

        return array(
            'states' => $states,
            'notif' => $this->getNotif()
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
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $region->getIcone();
            $fileName = $file->getClientOriginalName();

            $iconsDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/icons';
            $file->move($iconsDir, $fileName);

            $region->setIcone($fileName);
            $em->persist($region);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'La région a bien été ajoutée.');

            return $this->redirectToRoute('_create_region');
        }

        return array('form' => $form->createView(), 'notif' => $this->getNotif());
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
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $region->getIcone();
            $fileName = $file->getClientOriginalName();

            $iconsDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/icons';
            $file->move($iconsDir, $fileName);

            $region->setIcone($fileName);

            if(!$region->getPays()->getPublication()) {
                $region->setPublication(false);
            }

            $em->persist($region);

            $this->get('check_datas')->checkPublicationDomains($em, $region);
            $this->get('check_datas')->checkPublicationWines($em, $region);

            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'La région a bien été ajoutée.');

            return $this->redirectToRoute('_view_regions', array('page' => 1));
        }

        return array('form' => $form->createView(), 'notif' => $this->getNotif());
    }

    /**
     * @Route("/admin/regions/view/{sort}/{page}", name="_view_regions", defaults={"page": 1, "sort": "nom"})
     * @Template("AlastynAdminBundle:Region:viewRegions.html.twig")
     */
    /*
    public function viewRegionAction($page, $sort){
        $valid_sort = array('nom', 'pays', 'publication');
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        } else if(!in_array($sort, $valid_sort)) {
            throw new Exception('La classification de la table Region n\'est pas valide.');
        }

        $em = $this->getDoctrine()->getManager();
        $region = $em->createQueryBuilder()
            ->select('region')
            ->from('AlastynAdminBundle:Region','region');
        switch ($sort) {
            case 'pays':
                $region = $region->join('region.pays', 'pays')->orderBy('pays.abbr', 'ASC');
                break;
            case 'publication':
                $region = $region->orderBy('region.publication', 'ASC')->orderBy('region.nom', 'ASC');
                break;
            default:
                $region = $region->orderBy('region.nom', 'ASC');
                break;
        }
            
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
            'pagination' => $pagination,
            'sort' => $sort,
            'notif' => $this->getNotif()
        );
    }*/

    /**
     * @Route("/admin/regions/view", name="_view_regions")
     * @Template("AlastynAdminBundle:Region:viewRegions.html.twig")
     */
    public function viewRegionAction(){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $regions = $em->createQueryBuilder()
            ->select('region')
            ->from('AlastynAdminBundle:Region','region')
            ->getQuery()
            ->getResult();

        return array('regions' => $regions, 'notif' => $this->getNotif());
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

        return array('form' => $form->createView(), 'notif' => $this->getNotif());
    }

    /**
     * @Route("/admin/domain/list", name="_list_domain")
     * @Template("AlastynAdminBundle:Domaine:listDomain.html.twig")
     */
    public function listDomainAction(){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $domains = $em->createQueryBuilder()
            ->select('domaine')
            ->from('AlastynAdminBundle:Domaine','domaine')
            ->getQuery()
            ->getResult();

        return array(
            'domains' => $domains,
            'notif' => $this->getNotif()
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
            if(!$domain->getRegion()->getPublication()) {
                $domain->setPublication(false);
            }
            $em->persist($domain);

            $this->get('check_datas')->checkPublicationFlows($em, $domain);

            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le domaine a bien été ajouté.');

            return $this->redirectToRoute('_list_domain');
        }

        return array('form' => $form->createView(), 'notif' => $this->getNotif());
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
            $check_rss = $this->get('check_rss')->checkRss($flow->getUrl());
            $flow->setStatut($check_rss);
            if($check_rss != 'Valide') {
                $flow->setPublication(false);
            }
            $em->persist($flow);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le flux RSS a bien été ajouté.');

            return $this->redirectToRoute('_create_flow');
        }

        return array('form' => $form->createView(), 'notif' => $this->getNotif());
    }

    /**
     * @Route("/admin/flow/list", name="_list_flow")
     * @Template("AlastynAdminBundle:Flux:listFlow.html.twig")
     */
    public function listFlowAction(){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $flows = $em->createQueryBuilder()
            ->select('flux')
            ->from('AlastynAdminBundle:Flux','flux')
            ->getQuery()
            ->getResult();

        return array(
            'flows' => $flows,
            'notif' => $this->getNotif()
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
            $check_rss = $this->get('check_rss')->checkRss($flow->getUrl());
            $flow->setStatut($check_rss);
            if($check_rss != 'Valide' || !$flow->getDomaine()->getPublication()) {
                $flow->setPublication(false);
            }
            $em->persist($flow);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'Le flux a bien été ajouté.');

            return $this->redirectToRoute('_list_flow');
        }

        return array('form' => $form->createView(), 'notif' => $this->getNotif());
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

        return array('form' => $form->createView(), 'notif' => $this->getNotif());
    }

    /**
     * @Route("/admin/wine/list", name="_list_wine")
     * @Template("AlastynAdminBundle:Appellation:listWine.html.twig")
     */
    public function listWineAction(){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }

        $em = $this->getDoctrine()->getManager();
        $wines = $em->createQueryBuilder()
            ->select('appellation')
            ->from('AlastynAdminBundle:Appellation','appellation')
            ->getQuery()
            ->getResult();

        return array(
            'wines' => $wines,
            'notif' => $this->getNotif()
        );
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
            if(!$wine->getRegion()->getPublication()) {
                $wine->setPublication(false);
            }
            $em->persist($wine);
            $em->flush();

            $req->getSession()->getFlashBag()->add('notice', 'L\'appellation a bien été ajoutée.');

            return $this->redirectToRoute('_list_wine');
        }

        return array('form' => $form->createView(), 'notif' => $this->getNotif());
    }

    /**
     * @Route("/admin/wine/delete/{id}", name="_delete_wine")
     */
    public function deleteWineAction(Request $req, $id){
        $em = $this->getDoctrine()->getManager();
        $wine = $em->getRepository('AlastynAdminBundle:Appellation')->find($id);

        $em->remove($wine);
        $em->flush();

        return $this->redirectToRoute('_list_wine');
    }

    /**
     * @Route("/admin/suggestions/view", name="_view_suggests")
     * @Template("AlastynAdminBundle:Suggestion:viewSuggestions.html.twig")
     */
    public function viewSuggestionAction(){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }
        $em = $this->getDoctrine()->getManager();
        $suggestions = $em->createQueryBuilder()
            ->select('suggestion')
            ->from('AlastynAdminBundle:Suggestion','suggestion')
            ->getQuery()
            ->getResult();

        /*    
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
        */

        return array(
            'suggestions' => $suggestions,
            //'pagination' => $pagination,
            'notif' => $this->getNotif()
        );
    }

    /**
     * @Route("/admin/suggestion/view/{id}", name="_single_suggest")
     * @Template("AlastynAdminBundle:Suggestion:singleSuggestion.html.twig")
     */
    public function viewSingleSuggestionAction($id){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }
        $em = $this->getDoctrine()->getManager();
        $suggestion = $em->getRepository('AlastynAdminBundle:Suggestion')->find($id);

        return array(
            'suggestion' => $suggestion,
            'notif' => $this->getNotif()
        );
    }

    /**
     * @Route("/admin/suggestion/check/{id}", name="_check_suggest")
     * @Template("AlastynAdminBundle:Suggestion:checkSuggestion.html.twig")
     */
    public function checkSuggestionAction(Request $req, $id){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }
        $em = $this->getDoctrine()->getManager();
        $suggest = $em->getRepository('AlastynAdminBundle:Suggestion')->find($id);
        $nomD = $suggest->getNomDomaine();

        $form = $this->get('form.factory')->create(SuggestionCheckType::class, $suggest, array('nomdomaine' => $nomD));

        if($form->handleRequest($req)->isValid()){
            if($suggest->getDomaine() != null){
                $domaine = $em->getRepository('AlastynAdminBundle:Domaine')->findOneByNom($nomD);
            }
            else{
                $domaine = new Domaine();
                $domaine->setNom($nomD);
                $domaine->setAdresse($suggest->getAdresse());
                $domaine->setVille($suggest->getVille());
                $domaine->setWebsite($suggest->getSite());
                $domaine->setCodepostal($suggest->getCodepostal());
                $domaine->setRegion($suggest->getRegion());
                $domaine->setPublication($domaine->getRegion()->getPublication());

                $em->persist($domaine);
            }
            $flow = new Flux;
            $flow->setUrl($suggest->getRss());
            $check_rss = $this->get('check_rss')->checkRss($flow->getUrl());
            $flow->setStatut($check_rss);
            if($check_rss != 'Valide') {
                $flow->setPublication(false);
            }
            else{
                $flow->setPublication($domaine->getPublication());
            }
            $flow->setDomaine($domaine);

            $em->remove($suggest);

            $em->persist($flow);
            $em->flush();

            return $this->redirectToRoute('_view_suggests', array('page' => 1));
        }

        return array('form' => $form->createView(), 'notif' => $this->getNotif());
    }

    /**
     * @Route("/admin/suggestion/delete/{id}", name="_delete_suggest")
     */
    public function deleteSuggestionAction($id){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux administateurs authentifiés.');
        }
        $em = $this->getDoctrine()->getManager();
        $suggestion = $em->getRepository('AlastynAdminBundle:Suggestion')->find($id);
        $em->remove($suggestion);
        $em->flush();

        return $this->redirectToRoute('_view_suggests');
    }

    public function getNotif(){
        $notif = $this->getDoctrine()
            ->getManager()
            ->getRepository('AlastynAdminBundle:Suggestion')
            ->createQueryBuilder('s')
            ->select('COUNT(s)')
            ->getQuery()
            ->getSingleScalarResult();

        return $notif;
    }


}
