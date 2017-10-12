<?php

namespace AppBundle\Entity;


class User
{

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