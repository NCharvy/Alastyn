<?php

namespace Alastyn\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends Controller{
    public function loginAction(Request $req){
        if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')){
            return $this->redirectToRoute('/admin');
        }
        $authUtils = $this->get('security.authentication_utils');

        return $this->render('AlastynUserBundle:Security:login.html.twig', array(
            'last_username' => $authUtils->getLastUsername(),
            'error'			=> $authUtils->getLastAuthenticationError()
        ));
    }

    public function logoutAction(Request $req){
        $this->get('security.authorization_checker')->setToken(null);
        $this->get('req')->getSession()->invalidate();

        return $this->redirectToRoute('/index');
    }
}