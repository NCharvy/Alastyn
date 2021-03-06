<?php

namespace Alastyn\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Suggestion
 *
 * @ORM\Table(name="suggestion")
 * @ORM\Entity(repositoryClass="Alastyn\AdminBundle\Repository\SuggestionRepository")
 */
class Suggestion
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
     * @ORM\Column(name="rss", type="string", length=255)
     */
    private $rss;

    /**
     * @var string
     *
     * @ORM\Column(name="site", type="string", length=255)
     */
    private $site;

    /**
     * @var bool
     *
     * @ORM\Column(name="domaine_existe", type="boolean", nullable=true)
     */
    private $domaine_existe;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_domaine", type="string", length=255)
     */
    private $nomDomaine;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=255,nullable=true)
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
     * @ORM\Column(name="ville", type="string", length=255, nullable=true)
     */
    private $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255, nullable=true)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="courriel", type="string", length=255)
     */
    private $courriel;

    /**
     * @ORM\ManyToOne(targetEntity="Alastyn\AdminBundle\Entity\Region", inversedBy="suggestions")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true) 
     */
    private $region;

    /**
     * @ORM\ManyToOne(targetEntity="Alastyn\AdminBundle\Entity\Domaine")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $domaine;


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
     * Set rss
     *
     * @param string $rss
     *
     * @return Suggestion
     */
    public function setRss($rss)
    {
        $this->rss = $rss;

        return $this;
    }

    /**
     * Get rss
     *
     * @return string
     */
    public function getRss()
    {
        return $this->rss;
    }

    /**
     * Set site
     *
     * @param string $site
     *
     * @return Suggestion
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return string
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set nomDomaine
     *
     * @param string $nomDomaine
     *
     * @return Suggestion
     */
    public function setNomDomaine($nomDomaine)
    {
        $this->nomDomaine = $nomDomaine;

        return $this;
    }

    /**
     * Get nomDomaine
     *
     * @return string
     */
    public function getNomDomaine()
    {
        return $this->nomDomaine;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     *
     * @return Suggestion
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
     * @return Suggestion
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
     * Set ville
     *
     * @param string $ville
     *
     * @return Suggestion
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
     * Set nom
     *
     * @param string $nom
     *
     * @return Suggestion
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
     * Set prenom
     *
     * @param string $prenom
     *
     * @return Suggestion
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set courriel
     *
     * @param string $courriel
     *
     * @return Suggestion
     */
    public function setCourriel($courriel)
    {
        $this->courriel = $courriel;

        return $this;
    }

    /**
     * Get courriel
     *
     * @return string
     */
    public function getCourriel()
    {
        return $this->courriel;
    }

    /**
     * Set domaineExiste
     *
     * @param boolean $domaineExiste
     *
     * @return Suggestion
     */
    public function setDomaineExiste($domaineExiste)
    {
        $this->domaine_existe = $domaineExiste;

        return $this;
    }

    /**
     * Get domaineExiste
     *
     * @return boolean
     */
    public function getDomaineExiste()
    {
        return $this->domaine_existe;
    }

    /**
     * Set region
     *
     * @param \Alastyn\AdminBundle\Entity\Region $region
     *
     * @return Suggestion
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
     * Set domaine
     *
     * @param \Alastyn\AdminBundle\Entity\Domaine $domaine
     *
     * @return Suggestion
     */
    public function setDomaine(\Alastyn\AdminBundle\Entity\Domaine $domaine = null)
    {
        $this->domaine = $domaine;

        return $this;
    }

    /**
     * Get domaine
     *
     * @return \Alastyn\AdminBundle\Entity\Domaine
     */
    public function getDomaine()
    {
        return $this->domaine;
    }
}
