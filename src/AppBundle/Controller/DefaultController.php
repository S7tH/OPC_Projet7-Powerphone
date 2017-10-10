<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        
        return $this->render('default/index.html.twig', array(
            'state' => sha1(uniqid(mt_rand(), true))
        ));
    }

    /**
     * @Route("/bilemo", name="admin")
     */
    public function adminAction()
    {
        return $this->render('default/admin.html.twig');
    }

    /**
     * @Route("/bilemo/auth", name="admin_auth")
     */
    public function adminAuthAction()
    {
            // To avoid the ?code= url. Can be done with Javascript.
            return $this->redirectToRoute('get_produces');
    }

    /**
     * @Route("/bilemo/produces", name="get_produces")
     */
    public function getProducesAction()
    {
        $response = $this->get('csa_guzzle.client.bilemo_api')->get($this->getParameter('bilemo_api_url').'/api/produces', array(
        
            'headers' => array('Authorization' => 'Bearer '.$this->getUser()->getUsername())
        )
    );
        $produces = $this->get('serializer')->deserialize($response->getBody()->getContents(), 'array', 'json');

        return $this->render('default/produces.html.twig', array(
            'produces' => $produces
        ));
    }

    /**
     * @Route("/bilemo/logout", name="logout")
     */
    public function logoutAction()
    {
        $response = $this->delete('csa_guzzle.client.facebook_api')->get($this->getParameter('facebook_api_url').'/me/permissions');

        return $this->redirectToRoute('homepage');

    }
}
