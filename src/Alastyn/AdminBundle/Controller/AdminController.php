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

        foreach ($resources as $rss) {
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
        }

        return array('feeds' => $feeds);
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
			
			if (@fopen($URL_SITE_RSS, 'r')) 
			{
				$URL_Verif = "Url valide";
				try {

					$reader = new Reader;

					// Return a resource
					$resource = $reader->download($URL_SITE_RSS);

					// Return the right parser instance according to the feed format
					$parser = $reader->getParser(
						$resource->getUrl(),
						$resource->getContent(),
						$resource->getEncoding()
					);

					// Return a Feed object
					$feed = $parser->execute();

					$Verif_RSS = "RSS Valide";
					
					array_push($array,array("id"=>(count($array)),"site"=>$URL_SITE_NAME,"rss"=>$URL_SITE_RSS)); 
					$textResponse = json_encode($array,JSON_PRETTY_PRINT);
					file_put_contents("saved_rss/listeLiens.json", $textResponse);
				}
				catch (PicoFeedException $e) {
					$Verif_RSS = "RSS Comporte des erreurs <br> ".$e."<br>";
				}
			}
			else 
			{
				$URL_Verif = "Url non valide";
				$Verif_RSS = "RSS non vérifié";
			}
		}
		else
		{
			$URL_Verif = "pas de donnée";
			$Verif_RSS = "pas de donnée"; 
		}			
        return array('file' => $array, 'Verif_Exist' => $URL_Verif, 'Verif_RSS' => $Verif_RSS);    

    
        /*$vari="saved_rss/Fichier_Lien_RSS.xml";

        if ($request->getMethod() == 'POST') 
        {
            $URL_SITE_NAME = $_POST["URL_VALEUR_NAME"];
            $URL_SITE_RSS = $_POST["URL_VALEUR_RSS"];
            $xml= simplexml_load_file($vari);
            $json = json_encode($xml);
            $array = json_decode($json,TRUE);
            array_push($array["site_web"],array("id"=>(count($array["site_web"])),"site"=>$URL_SITE_NAME,"rss"=>$URL_SITE_RSS));
            
            $_xml ="<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n";
            $_xml .="<flux>\r\n";
            
            for ($i = 0; $i < count($array["site_web"]); $i++)
            {
                $_xml .="   <site_web>\r\n\r\n";
                $_xml .="       <id>".$array["site_web"][$i]["id"]."</id>\r\n";
                $_xml .="       <site>".$array["site_web"][$i]["site"]."</site>\r\n";
                $_xml .="       <rss>".$array["site_web"][$i]["rss"]."</rss>\r\n\n";
                $_xml .="   </site_web>\r\n\r\n";            
            }
            
            $_xml .="</flux>\r\n";
            
            $file= fopen($vari, "w");
            fwrite($file,$_xml);
            fclose($file);

            return array('fichier_xml' => $array);          
        }
        else
        {
            $xml= simplexml_load_file($vari);
            $json = json_encode($xml);
            $array = json_decode($json,TRUE);
            return array('fichier_xml' => $array); 
        }
        */
    }

    /**
     * @Route("/admin/state/create", name="_create_state")
     * @Template("AlastynAdminBundle:Admin:createState.html.twig")
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
     * @Template("AlastynAdminBundle:Admin:createState.html.twig")
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
     * @Route("/admin/states/view", name="_view_states")
     * @Template("AlastynAdminBundle:Admin:viewStates.html.twig")
     */
    public function viewStateAction(Request $req){
        $em = $this->getDoctrine()->getManager();
        $states = $em->getRepository('AlastynAdminBundle:Pays')->findAll();
        return array('states' => $states);
    }

    /**
     * @Route("/admin/state/delete/{id}", name="_delete_state")
     */
    public function deleteStateAction(Request $req, $id){
        $em = $this->getDoctrine()->getManager();
        $state = $em->getRepository('AlastynAdminBundle:Pays')->find($id);

        $em->remove($state);
        $em->flush();

        return $this->redirectToRoute('_view_states');
    }

    /**
     * @Route("/admin/region/create", name="_create_region")
     * @Template("AlastynAdminBundle:Admin:createRegion.html.twig")
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
     * @Template("AlastynAdminBundle:Admin:createRegion.html.twig")
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
     * @Template("AlastynAdminBundle:Admin:viewRegions.html.twig")
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
}
