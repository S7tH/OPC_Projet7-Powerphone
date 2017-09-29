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
     * @Route("/admin", name="admin")
     */
    public function adminAction()
    {
        return $this->render('default/admin.html.twig');
    }

    /**
     * @Route("/admin/auth", name="admin_auth")
     */
    public function adminAuthAction()
    {
        // To avoid the ?code= url. Can be done with Javascript.
        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/admin/produces", name="get_produces")
     */
    public function getProducesAction()
    {
        $response = $this->get('csa_guzzle.client.bilemo_api')->get($this->getParameter('bilemo_api_url').'/produces',
        [
            'headers' => ['Authorization' => 'Bearer '.$this->getUser()->getUsername()]
        ]
    );
        $produces = $this->get('serializer')->deserialize($response->getBody()->getContents(), 'array', 'json');

        return $this->render('default/produces.html.twig', ['produces' => $produces]);
    }


    /**
     * @Route("/admin/logout", name="logout")
     */
    public function logoutAction()
    {
    }
}
