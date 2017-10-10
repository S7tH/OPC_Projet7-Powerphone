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
     * @Route("/bilemo/produces/{limit}/{offset}/{order}", name="get_produces")
     */
    public function getProducesAction($limit=7, $offset=0, $order='asc',Request $request)
    {
        //if search form is submitted
        if ($request->isMethod('POST') && $request->request->get('keyword') !== '')
        {
          $keyword = '&keyword='.$request->request->get('keyword');
        }
        else
        {
          $keyword = null;
        }
        
        $response = $this->get('csa_guzzle.client.bilemo_api')->get($this->getParameter('bilemo_api_url').'/api/produces?limit='.$limit.
        '&offset='.$offset.
        '&order='.$order.
        $keyword, array(
        
            'headers' => array('Authorization' => 'Bearer '.$this->getUser()->getUsername())
            )
        );
        $produces = $this->get('serializer')->deserialize($response->getBody()->getContents(), 'array', 'json');
/*
        // for pagination
        $totalPages = ceil($produces['meta']['total_items']/$produces['meta']['limit']);
        $actualPage = ceil($produces['meta']['offset']/$produces['meta']['limit']);
*/

        return $this->render('produces/produces.html.twig', array(
            'produces' => $produces
            /*,
            'totalPages'    =>  $totalPages,
            'actualPage'    =>  $actualPage,
            'limit'         =>  $produces['meta']['limit']
            */
        ));
    }

    /**
     * @Route("/bilemo/produce/{id}", name="get_produce", requirements = {"id"="\d+"})
     */
     public function getProduceAction($id)
     {
         
         $response = $this->get('csa_guzzle.client.bilemo_api')->get($this->getParameter('bilemo_api_url').'/api/produces/'.$id, array(
         
             'headers' => array('Authorization' => 'Bearer '.$this->getUser()->getUsername())
         )
     );
         $produce = $this->get('serializer')->deserialize($response->getBody()->getContents(), 'array', 'json');
 
         return $this->render('produces/produce.html.twig', array(
             'produce' => $produce
         ));
     }

    /**
     * @Route("/bilemo/users", name="get_users")
     */
     public function getUsersActionRequest()
     {


         $response = $this->get('csa_guzzle.client.bilemo_api')->get($this->getParameter('bilemo_api_url').'/api/user', array(
         
             'headers' => array('Authorization' => 'Bearer '.$this->getUser()->getUsername())
         )
     );
         $users = $this->get('serializer')->deserialize($response->getBody()->getContents(), 'array', 'json');
 
         return $this->render('users/users.html.twig', array(
             'users' => $users
         ));
     }

     /**
     * @Route("/bilemo/user/{id}", name="get_user", requirements = {"id"="\d+"})
     */
     public function getUserAction($id)
     {
         $response = $this->get('csa_guzzle.client.bilemo_api')->get($this->getParameter('bilemo_api_url').'/api/user/'.$id, array(
         
             'headers' => array('Authorization' => 'Bearer '.$this->getUser()->getUsername())
         )
     );
         $user = $this->get('serializer')->deserialize($response->getBody()->getContents(), 'array', 'json');
 
         return $this->render('users/user.html.twig', array(
             'user' => $user
         ));
     }

    /**
     * @Route("/bilemo/logout", name="logout")
     */
    public function logoutAction()
    {
    }
}
