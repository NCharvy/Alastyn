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
     * @ORM\OneToMany(targetEntity="Alastyn\AdminBundle\Entity\Domaine", mappedBy="region", cascade={"all"})
     */
    private $domaines;

    /**
     * @ORM\ManyToOne(targetEntity="Alastyn\AdminBundle\Entity\Pays", inversedBy="regions", cascade={"all"}) 
     */
    private $pays;


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
     * @param \Alastyn\AdminBundle\Domaine $domaine
     *
     * @return Region
     */
    public function addDomaine(\Alastyn\AdminBundle\Domaine $domaine)
    {
        $this->domaines[] = $domaine;

        return $this;
    }

    /**
     * Remove domaine
     *
     * @param \Alastyn\AdminBundle\Domaine $domaine
     */
    public function removeDomaine(\Alastyn\AdminBundle\Domaine $domaine)
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
     * @param \Alastyn\AdminBundle\Pays $pays
     *
     * @return Region
     */
    public function setPays(\Alastyn\AdminBundle\Pays $pays = null)
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * Get pays
     *
     * @return \Alastyn\AdminBundle\Pays
     */
    public function getPays()
    {
        return $this->pays;
    }
}
