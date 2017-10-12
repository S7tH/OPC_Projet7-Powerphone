<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


use AppBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;


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
     * @Route("/bilemo/admin", name="admin")
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
    public function getProducesAction($limit=15, $offset=0, $order='asc',Request $request)
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

        // for pagination
        $totalPages = ceil($produces['meta']['total_items']/$produces['meta']['limit']);
        $actualPage = ceil($produces['meta']['offset']/$produces['meta']['limit']);


        return $this->render('produces/produces.html.twig', array(
            'produces' => $produces,
            'totalPages'    =>  $totalPages,
            'actualPage'    =>  $actualPage,
            'limit'         =>  $produces['meta']['limit']
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
     * @Route("/bilemo/users/{limit}/{offset}/{order}", name="get_users")
     */
     public function getUsersActionRequest($limit=15, $offset=0, $order='asc',Request $request)
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

         $response = $this->get('csa_guzzle.client.bilemo_api')->get($this->getParameter('bilemo_api_url').'/api/user?limit='.$limit.
         '&offset='.$offset.
         '&order='.$order.
         $keyword, array(
         
             'headers' => array('Authorization' => 'Bearer '.$this->getUser()->getUsername())
         )
     );
         $users = $this->get('serializer')->deserialize($response->getBody()->getContents(), 'array', 'json');
        
         // for pagination
         $totalPages = ceil($users['meta']['total_items']/$users['meta']['limit']);
         $actualPage = ceil($users['meta']['offset']/$users['meta']['limit']);

         return $this->render('users/users.html.twig', array(
             'users' => $users,
             'totalPages'    =>  $totalPages,
             'actualPage'    =>  $actualPage,
             'limit'         =>  $users['meta']['limit']
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
     * @Route("/bilemo/user/create", name="create_user")
     */
     public function createUserAction(Request $request)
     {  
        //create a user object entity
        $user = new User();
        
        //create the formBuilder
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $user);

        $formBuilder
            ->add('email', EmailType::class, array(
                'required' => true,
                'label' => 'Email utilisé sur Facebook :  >'))
            ->add('save',  SubmitType::class, array('label' => 'Enregistrer'));

            $form= $formBuilder->getForm();


        //if a form has been send so we are not displaying the form but send the form and if the values are ok
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid())
        {   
            $data = array(
                'username'    => null,
                'facebook_id' => null,
                'email'       => $user->getEmail(),
                'gender'      => null,
                'roles'       => ['ROLE_USER']
            );

            try
            {
                $req = $this->get('csa_guzzle.client.bilemo_api')->post($this->getParameter('bilemo_api_url').'/api/user', array(
                    
                        'headers' => array(
                            'Authorization' => 'Bearer '.$this->getUser()->getUsername(),
                            'Content-Type' => 'application/json'
                        ),
                        'json' => $data
                    )
                );
            }
            catch (\GuzzleHttp\Exception\ClientException $e)
            {
                $error = json_decode($e->getResponse()->getBody()->getContents(), true);
                $form->addError(new FormError('Cet utilisateur est déjà inscrit'));
                return $this->render('users/create_user.html.twig', array(
                    'form'      => $form->createView(),
                ));
            }
           
            $response = json_decode($req->getBody()->getContents(), true);

                 

            $request->getSession()->getFlashBag()->add('notice', 'Utilisateur bien enregistré.');
        
            // We are displaying now the trick introduce page thanks a redirection to its route.
            return $this->redirectToRoute('get_users');
        }
        
        //if any send, we are displaying the form
        return $this->render('users/create_user.html.twig',array(
            'form' => $form->createView(),
        ));

     }


    /**
     * @Route("/bilemo/logout", name="logout")
     */
    public function logoutAction()
    {
    }
}
