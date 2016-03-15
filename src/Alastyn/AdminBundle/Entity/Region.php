<?php

namespace Alastyn\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Region
 *
 * @ORM\Table(name="region")
 * @ORM\Entity(repositoryClass="Alastyn\AdminBundle\Repository\RegionRepository")
 */
class Region
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
     * @ORM\OneToMany(targetEntity="Alastyn\AdminBundle\Entity\Domaine", mappedBy="region", nullable=true)
     */
    private $domaines;

    /**
     * @ORM\ManyToOne(targetEntity="Alastyn\AdminBundle\Entity\Pays", inversedBy="regions", nullable=true) 
     */
    private $pays;

    /**
     * @ORM\OneToMany(targetEntity="Alastyn\AdminBundle\Entity\Appellation", mappedBy="region", nullable=true)
     */
    private $appellations;


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
     * @return Region
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
     * Constructor
     */
    public function __construct()
    {
        $this->domaines = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add domaine
     *
     * @param \Alastyn\AdminBundle\Entity\Domaine $domaine
     *
     * @return Region
     */
    public function addDomaine(\Alastyn\AdminBundle\Entity\Domaine $domaine)
    {
        $this->domaines[] = $domaine;

        return $this;
    }

    /**
     * Remove domaine
     *
     * @param \Alastyn\AdminBundle\Entity\Domaine $domaine
     */
    public function removeDomaine(\Alastyn\AdminBundle\Entity\Domaine $domaine)
    {
        $this->domaines->removeElement($domaine);
    }

    /**
     * Get domaines
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDomaines()
    {
        return $this->domaines;
    }

    /**
     * Set pays
     *
     * @param \Alastyn\AdminBundle\Entity\Pays $pays
     *
     * @return Region
     */
    public function setPays(\Alastyn\AdminBundle\Entity\Pays $pays = null)
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * Get pays
     *
     * @return \Alastyn\AdminBundle\Entity\Pays
     */
    public function getPays()
    {
        return $this->pays;
    }

    /**
     * Add appellation
     *
     * @param \Alastyn\AdminBundle\Entity\Appellation $appellation
     *
     * @return Region
     */
    public function addAppellation(\Alastyn\AdminBundle\Entity\Appellation $appellation)
    {
        $this->appellations[] = $appellation;

        return $this;
    }

    /**
     * Remove appellation
     *
     * @param \Alastyn\AdminBundle\Entity\Appellation $appellation
     */
    public function removeAppellation(\Alastyn\AdminBundle\Entity\Appellation $appellation)
    {
        $this->appellations->removeElement($appellation);
    }

    /**
     * Get appellations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAppellations()
    {
        return $this->appellations;
    }
}
