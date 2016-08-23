<?php

namespace Simplon\BlogBundle\Entity;

/**
 * Login
 */
class Login
{
    /**
     * @var username
     */
    private $username;

    /**
     * @var string
     */
    private $password;


    /**
     * Set username
     *
     * @param  string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }


    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}

