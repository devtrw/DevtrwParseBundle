<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\ParseBundle\Object;

/**
 * Class User
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Devtrw\Object
 */
class User extends AbstractParseObject
{
    public static function getClassPath()
    {
        return parent::getClassPath() . 's';
    }

    /**
     * {@inheritdoc}
     */
    public function getDataFields()
    {
        return ['username', 'password', 'emailVerified', 'email'];
    }

    protected $emailVerified;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->get('email');
    }

    /**
     * @return boolean
     */
    public function getEmailVerified()
    {
        return (bool) $this->get('emailVerified');
    }

    /**
     * @param boolean $emailVerified
     *
     * @return $this
     */
    public function setEmailVerified($emailVerified)
    {
        $this->set('emailVerified', (bool) $emailVerified);

        return $this;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->set('email', $email);

        return $this;
    }

    public function setPassword($password)
    {
        $this->set('password', $password);

        return $this;
    }

    public function setUsername($username)
    {
        $this->set('username', $username);

        return $this;
    }
}
