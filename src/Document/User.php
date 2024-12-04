<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;

#[Document(collection: "users")]
class User
{
    #[Id]
    private $id;

    #[Field(type: "string")]
    private $name;

    #[Field(type: "string")]
    private $email;

    #[Field(type: "string")]
    private $password;

    #[Field(type: "string")]
    private $school_id;

    #[Field(type: "string")]
    private $refNum;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getSchoolId()
    {
        return $this->school_id;
    }

    /**
     * @param mixed $school_id
     */
    public function setSchoolId($school_id): void
    {
        $this->school_id = $school_id;
    }

    /**
     * @return mixed
     */
    public function getRefNum()
    {
        return $this->refNum;
    }

    /**
     * @param mixed $refNum
     */
    public function setRefNum($refNum): void
    {
        $this->refNum = $refNum;
    }

}