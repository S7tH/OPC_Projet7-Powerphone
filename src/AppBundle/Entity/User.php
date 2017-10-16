<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class User
{
  /**
   * @Assert\Email()
   */
  private $email;


  // Les getters et setters
  public function getEmail()
  {
      return $this->email;
  }

  public function setEmail($email)
  {
      $this->email = $email;

      return $this;
  }
}