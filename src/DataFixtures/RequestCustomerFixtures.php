<?php

namespace App\DataFixtures;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Hotel;
use App\Entity\User;
use App\Entity\RequestCustomer;
use Faker\Factory;

class RequestCustomerFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $futureCustomer = $manager->getRepository(User::class)->findBy([
            'isCustomer' => 0
        ]);

        foreach ($futureCustomer as $user) {
            $requestCustomer = new RequestCustomer();
            $requestCustomer->setUser($user);
            $requestCustomer->setHotel($user->getHotels()[0]);
            $requestCustomer->setCreatedAt(new DateTime());
            $manager->persist($requestCustomer);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixtures::class, HotelFixtures::class];
    }
}
