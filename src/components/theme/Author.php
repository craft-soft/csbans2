<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\components\theme;

class Author
{
    private ?string $role = null;
    private ?string $email = null;
    private ?string $contacts = null;
    private ?string $nickName = null;
    private ?string $lastName = null;
    private ?string $firstName = null;

    /**
     * Author's popular nickname
     * @return string
     */
    public function getNickName(): ?string
    {
        return $this->nickName;
    }

    /**
     * Author's first name
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Author's last name
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Author's e-mail address
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Author's additional contacts. Allow any HTML code, except javascript. All javascript will be cut out.
     * @return string
     */
    public function getContacts(): ?string
    {
        return $this->contacts;
    }

    /**
     * Author's role in this theme project
     * @return string
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string|null $nickName
     * @return Author
     */
    public function setNickName(?string $nickName): Author
    {
        $this->nickName = $nickName;
        return $this;
    }

    /**
     * @param string|null $firstName
     * @return Author
     */
    public function setFirstName(?string $firstName): Author
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @param string|null $lastName
     * @return Author
     */
    public function setLastName(?string $lastName): Author
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @param string|null $email
     * @return Author
     */
    public function setEmail(?string $email): Author
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param string|null $contacts
     * @return Author
     */
    public function setContacts(?string $contacts): Author
    {
        $this->contacts = $contacts;
        return $this;
    }

    /**
     * @param string|null $role
     * @return Author
     */
    public function setRole(?string $role): Author
    {
        $this->role = $role;
        return $this;
    }
}