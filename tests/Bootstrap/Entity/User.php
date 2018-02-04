<?php

namespace Webit\Tests\Bootstrap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var Address[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Webit\Tests\Bootstrap\Entity\Address", mappedBy="user")
     */
    private $addresses;

    public function __construct($name)
    {
        $this->name = $name;
        $this->addresses = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return nstring
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function rename($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $street
     * @param string $postCode
     * @param string $city
     * @return Address
     */
    public function addAddress($street, $postCode, $city)
    {
        $this->addresses->add($address = new Address($this, $street, $postCode, $city));

        return $address;
    }

    /**
     * @return Address[]
     */
    public function addresses()
    {
        return $this->addresses->toArray();
    }
}
