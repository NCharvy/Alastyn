<?php
    namespace Alastyn\AdminBundle\Entity;

    use Doctrine\ORM\Tools\Pagination\Paginator;
    use Doctrine\ORM\EntityManager;

    class Pagination{
        private $page;
        private $maxperpage;
        private $query;

        public function __construct($query, $page = 1, $maxperpage = 5)
        {
            $this->page = $page;
            $this->maxperpage = $maxperpage;
            $this->query = $query;
        }
        /**
         * Get the paginated list of published articles
         *
         * @param int $page
         * @param int $maxperpage
         * @param string $sortby
         * @return Paginator
         */
        public function getList()
        {
            $query = $this->getQuery()->setFirstResult(($this->getPage()-1) * $this->getMaxPerPage())
                ->setMaxResults($this->getMaxPerPage());

            return new Paginator($query);
        }

        public function getPage()
        {
            return $this->page;
        }

        public function setPage($page)
        {
            $this->page = $page;
        }

        public function getMaxPerPage()
        {
            return $this->maxperpage;
        }

        public function setMaxPerPage($maxperpage)
        {
            $this->maxperpage = $maxperpage;
        }

        public function getQuery()
        {
            return $this->query;
        }

        public function setQuery($query)
        {
            $this->query = $query;
        }
    }