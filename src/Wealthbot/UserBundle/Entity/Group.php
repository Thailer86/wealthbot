<?php

namespace Wealthbot\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Wealthbot\UserBundle\Entity\Group.
 */
class Group
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;


    private $group_name;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var int
     */
    private $owner_id;

    /**
     * @var \Wealthbot\UserBundle\Entity\User
     */
    private $owner;

    const GROUP_NAME_ALL = 'All';

    public function __construct($name = null, $roles = [])
    {
        $this->users = new ArrayCollection();

        $this->roles = [];
        $this->group_name = $name;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Group
     */
    public function setGroupName($name)
    {
        $this->group_name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->group_name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * Set roles.
     *
     * @param array $roles
     *
     * @return Group
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles.
     *
     * @return array
     */
    public function getRoles()
    {
        if(!$this->roles){
            return [];
        };
        return $this->roles;
    }

    /**
     * Add users.
     *
     * @param \Wealthbot\UserBundle\Entity\User $users
     *
     * @return Group
     */
    public function addUser(\Wealthbot\UserBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users.
     *
     * @param \Wealthbot\UserBundle\Entity\User $users
     */
    public function removeUser(\Wealthbot\UserBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set owner_id.
     *
     * @param int $ownerId
     *
     * @return Group
     */
    public function setOwnerId($ownerId)
    {
        $this->owner_id = $ownerId;

        return $this;
    }

    /**
     * Get owner_id.
     *
     * @return int
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * Set owner.
     *
     * @param \Wealthbot\UserBundle\Entity\User $owner
     *
     * @return Group
     */
    public function setOwner(\Wealthbot\UserBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return \Wealthbot\UserBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    public function isAll()
    {
        return $this->group_name === self::GROUP_NAME_ALL;
    }

    public function getRiaCount($riaId)
    {
        $count = 0;

        /** @var User $user */
        foreach ($this->users as $user) {
            if (($user->hasRole('ROLE_RIA_ADMIN') || $user->hasRole('ROLE_RIA_USER')) && $user->getProfile()->getRia()->getId() === $riaId) {
                ++$count;
            }
        }

        return $count;
    }
}
