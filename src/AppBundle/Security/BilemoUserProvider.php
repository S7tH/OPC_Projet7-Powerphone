<?php

namespace AppBundle\Security;

use GuzzleHttp\Client;
use JMS\Serializer\Serializer;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class BilemoUserProvider implements UserProviderInterface
{
    private $client;

    public function __construct(Client $client, Serializer $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    public function loadUserByUsername($username)
    {
        $url = 'http://127.0.0.1:8001/user-check/'.$username;

        $response = $this->client->get($url);
        
        $res = $response->getBody()->getContents();
        $userData = $this->serializer->deserialize($res, 'array', 'json');
        
        if (!$userData)
        {
            throw new \LogicException('Did not managed to get your user info from Github.');
        }
        $user = new User($username, null);
        
        return $user;
    }
        
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class))
        {
            throw new UnsupportedUserException();
        }
        return $user;
    }
        
    public function supportsClass($class)
    {
        return 'Symfony\Component\Security\Core\User\User' === $class;
    }
}