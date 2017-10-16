<?php

namespace AppBundle\Security;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FacebookAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    private $client;
    private $clientId;
    private $clientSecret;
    private $router;

    public function __construct(Client $client, $clientId, $clientSecret, Router $router)
    {
        $this->client = $client;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->router = $router;
    }
    //1 call of symfony after that the serv returned a code
    public function createToken(Request $request, $providerKey)
    {
        $code = $request->query->get('code');
        $redirectUri = $this->router->generate('admin_auth', [], ROUTER::ABSOLUTE_URL);
        $url = 'https://graph.facebook.com/v2.10/oauth/access_token?client_id='.$this->clientId.'&client_secret='.$this->clientSecret.'&redirect_uri='.urlencode($redirectUri).'&code='.$code;
        
        $response = $this->client->get($url);
        
        $res = $response->getBody()->getContents();
       
        $info = explode(':', $res);
        
        $access = explode(',', $info[1]);
       
        $res = explode('"', $access[0]);

        if (isset($res[1]) && 'error' == $res[1])
        {
            throw new BadCredentialsException('No access_token returned by Facebook. Start over the process.');
        }
        return new PreAuthenticatedToken(
            'anon.',
            $res[1],
            $providerKey
        );
    }
    
    //2
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }
    
    //3 after send the user log, we check if he exists
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $accessToken = $token->getCredentials();
        $user = $userProvider->loadUserByUsername($accessToken);
        return new PreAuthenticatedToken(
            $user,
            $accessToken,
            $providerKey,
            ['ROLE_USER']
        );
    }
    
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response("Authentication Failed :(", 403);
    }
}