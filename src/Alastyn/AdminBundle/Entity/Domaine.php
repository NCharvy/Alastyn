<?php

namespace Alastyn\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Domaine
 *
 * @ORM\Table(name="domaine")
 * @ORM\Entity(repositoryClass="Alastyn\AdminBundle\Repository\DomaineRepository")
 */
class Domaine
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
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=255, nullable=true)
     */
    private $adresse;

    /**
     * @var string
     *
     * @ORM\Column(name="codepostal", type="string", length=255, nullable=true)
     */
    private $codepostal;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=255, nullable=true)
     */
    private $ville;

    /**
     * @ORM\OneToMany(targetEntity="Alastyn\AdminBundle\Entity\Flux", mappedBy="domaine", cascade={"all"})
     */
    private $flux;

    /**
     * @ORM\ManyToOne(targetEntity="Alastyn\AdminBundle\Entity\Region", inversedBy="domaines", cascade={"all"}) 
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
     * @return Domaine
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
     * Set adresse
     *
     * @param string $adresse
     *
     * @return Domaine
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set codepostal
     *
     * @param string $codepostal
     *
     * @return Domaine
     */
    public function setCodepostal($codepostal)
    {
        $this->codepostal = $codepostal;

        return $this;
    }

    /**
     * Get codepostal
     *
     * @return string
     */
    public function getCodepostal()
    {
        return $this->codepostal;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return Domaine
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set ville
     *
     * @param string $ville
     *
     * @return Domaine
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string
     */
    public function getVille()
    {
        return $this->ville;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->flux = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add flux
     *
     * @param \Alastyn\AdminBundle\Flux $flux
     *
     * @return Domaine
     */
    public function addFlux(\Alastyn\AdminBundle\Flux $flux)
    {
        $this->flux[] = $flux;

        return $this;
    }

    /**
     * Remove flux
     *
     * @param \Alastyn\AdminBundle\Flux $flux
     */
    public function removeFlux(\Alastyn\AdminBundle\Flux $flux)
    {
        $this->flux->removeElement($flux);
    }

    /**
     * Get flux
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFlux()
    {
        return $this->flux;
    }

    /**
     * Set region
     *
     * @param \Alastyn\AdminBundle\Entity\Region $region
     *
     * @return Domaine
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
}
