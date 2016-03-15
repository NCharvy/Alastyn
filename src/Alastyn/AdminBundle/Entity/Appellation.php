<?php

namespace Alastyn\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Appellation
 *
 * @ORM\Table(name="appellation")
 * @ORM\Entity(repositoryClass="Alastyn\AdminBundle\Repository\AppellationRepository")
 */
class Appellation
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var bool
     *
     * @ORM\Column(name="publication", type="boolean")
     */
    private $publication;

    /**
     * @ORM\ManyToOne(targetEntity="Alastyn\AdminBundle\Entity\Region", inversedBy="appellations") 
     */
    private $region;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Appellation
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set region
     *
     * @param \Alastyn\AdminBundle\Entity\Region $region
     *
     * @return Appellation
     */
    public function setRegion(\Alastyn\AdminBundle\Entity\Region $region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return \Alastyn\AdminBundle\Entity\Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set publication
     *
     * @param boolean $publication
     *
     * @return Appellation
     */
    public function setPublication($publication)
    {
        $this->publication = $publication;

        return $this;
    }

    /**
     * Get publication
     *
     * @return boolean
     */
    public function getPublication()
    {
        return $this->publication;
    }
}
