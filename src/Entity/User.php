<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\UserModel;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table('users')]
#[ORM\Index(columns: ['created_at'])]
#[ORM\Index(columns: ['last_login'])]
#[ORM\Index(columns: ['is_enabled'])]
#[UniqueEntity(fields: ['email_canonical'], errorPath: 'email', message: 'Email is already exists')]
#[UniqueEntity(fields: ['username_canonical'], errorPath: 'username', message: 'Username is already exists')]
class User extends UserModel
{
    use ColumnTrait\Id;

    public function __construct()
    {
        parent::__construct();

        $this->is_enabled = true;
        $this->email = '';
    }

    public function __toString(): string
    {
        return $this->getUsername();
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsernameCanonical();
    }
}
