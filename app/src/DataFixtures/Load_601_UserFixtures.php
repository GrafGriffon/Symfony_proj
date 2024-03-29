<?php

namespace App\DataFixtures;

use App\Entity\Supply;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;

class Load_601_UserFixtures extends AppFixtures
{
    public function load(ObjectManager $manager): void
    {
        $suppluyers = $manager->getRepository(Supply::class)->findAll();
        $len=count($suppluyers);
        for ($i = 0; $i < 100; $i++) {
            $user = (new User())
                ->setEmail($this->generator->email)
                ->setFirstName($this->generator->firstName)
                ->setLastName($this->generator->lastName)
                ->setPassword($this->generator->password)
                ->setRoles(['ROLE_ADMIN'])
                ->setStatus($this->generator->numberBetween(0, 1));
            if (rand(0,2)==0){
                $user->setSupply($suppluyers[rand(0, $len-1)])
                ->setRoles(['ROLE_ADMIN', 'ROLE_SUPPLY']);
            }
            $manager->persist($user);
        }

        $manager->flush();
    }
}