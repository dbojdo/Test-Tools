<?php

namespace Webit\Tests\Bootstrap\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_addresses")
 */
class Address
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Webit\Tests\Bootstrap\Entity\User", inversedBy="addresses")
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $street;

    /**
     * @var string
     * @ORM\Column(type="string", length=16)
     */
    private $postCode;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    public function __construct(User $user, $street, $postCode, $city)
    {
        $this->user = $user;
        $this->street = $street;
        $this->postCode = $postCode;
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function street()
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function postCode()
    {
        return $this->postCode;
    }

    /**
     * @return string
     */
    public function city()
    {
        return $this->city;
    }
}