<?php
namespace Alastyn\AdminBundle\Services;

class CheckDatas
{
    public function checkPublicationRegions($em, $state) {
        $regions = $em->getRepository('AlastynAdminBundle:Region')->findByPays($state->getId());
        foreach ($regions as $region) {
            $region->setPublication($state->getPublication());
            $em->persist($region);

            $this->checkPublicationDomains($em, $region);
            $this->checkPublicationWines($em, $region);
        }
    }

    public function checkPublicationDomains($em, $region) {
        $domains = $em->getRepository('AlastynAdminBundle:Domaine')->findByRegion($region->getId());
        foreach ($domains as $domain) {
            $domain->setPublication($region->getPublication());
            $em->persist($domain);

            $this->checkPublicationFlows($em, $domain);
        }
    }

    public function checkPublicationWines($em, $region) {
        $wines = $em->getRepository('AlastynAdminBundle:Appellation')->findByRegion($region->getId());
        foreach ($wines as $wine) {
            $wine->setPublication($region->getPublication());
            $em->persist($wine);
        }
    }

    public function checkPublicationFlows($em, $domain) {
        $flows = $em->getRepository('AlastynAdminBundle:Flux')->findByDomaine($domain->getId());
        foreach ($flows as $flow) {
            if($flow->getStatut() != 'Valide') {
                $flow->setPublication(false);
            } else {
                $flow->setPublication($domain->getPublication());
            }
            $em->persist($flow);
       }
    }
}