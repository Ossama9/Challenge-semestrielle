<?php

namespace App\DataFixtures;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Hotel;
use App\Entity\User;
use Faker\Factory;

class HotelFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $customers = $manager->getRepository(User::class)->findBy([
            'isCustomer' => 1
        ]);
        $futureCustomer = $manager->getRepository(User::class)->findBy([
            'isCustomer' => 0
        ]);
        $faker = Factory::create('fr_FR');
        $nbHotels = 12;
        $nbHotelsOfFutureCustomer = 3;

        /* for ($i = 1; $i <= $nbHotels; $i++) { */
        foreach ($customers as $customer) {
            $hotel = new Hotel();
            $hotel->setIban($faker->iban('FR'));
            $hotel->setName($faker->company);
            $hotel->setAdresse($faker->address);
            $hotel->setVille($faker->city);
            $hotel->setCountry($faker->country);
            $hotel->setCodePostal((int) $faker->postcode);
            $hotel->setUser($customer);
            $hotel->setNote($faker->numberBetween(0, 5));
            $hotel->setCreatedAt(new DateTime());
            $hotel->setUpdatedAt(new DateTime());
            $hotel->setImage($faker->imageUrl(800, 800, 'Nature', true, 'Faker'));
            $hotel->setTelephone($faker->phoneNumber);
            $hotel->setIsValidated(1);
            $manager->persist($hotel);
        }

        foreach ($futureCustomer as $user) {
            $hotel = new Hotel();
            $hotel->setIban($faker->iban('FR'));
            $hotel->setName($faker->company);
            $hotel->setAdresse($faker->address);
            $hotel->setVille($faker->city);
            $hotel->setCountry($faker->country);
            $hotel->setCodePostal((int) $faker->postcode);
            $hotel->setUser($user);
            $hotel->setNote($faker->numberBetween(0, 5));
            $hotel->setCreatedAt(new DateTime());
            $hotel->setUpdatedAt(new DateTime());
            $hotel->setImage($faker->imageUrl(800, 800, 'LandScape', true, 'Faker', true));
            $hotel->setTelephone($faker->phoneNumber);
            $manager->persist($hotel);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixtures::class];
    }
}
