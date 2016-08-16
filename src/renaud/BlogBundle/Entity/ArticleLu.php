<?php

namespace renaud\BlogBundle\Entity;

/**
 * ArticleLu
 */
class ArticleLu
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var bool
     */
    private $lu;

    /**
     * @var int
     */
    private $article;

    /**
     * @var int
     */
    private $user;


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
     * Set lu
     *
     * @param boolean $lu
     *
     * @return ArticleLu
     */
    public function setLu($lu)
    {
        $this->lu = $lu;

        return $this;
    }

    /**
     * Get lu
     *
     * @return bool
     */
    public function getLu()
    {
        return $this->lu;
    }

    /**
     * Set article
     *
     * @param integer $article
     *
     * @return ArticleLu
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return int
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set user
     *
     * @param integer $user
     *
     * @return ArticleLu
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }
}

