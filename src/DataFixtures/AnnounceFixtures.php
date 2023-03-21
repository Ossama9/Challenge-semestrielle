<?php

namespace App\DataFixtures;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Announce;
use App\Entity\Hotel;
use Faker\Factory;

class AnnounceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $hotel = $manager->getRepository(Hotel::class)->findAll();
        $faker = Factory::create('fr_FR');
        $nbAnnounces = 15;

        for ($i = 0; $i < $nbAnnounces; $i++) {
            $announce = new Announce();
            $announce->setTitle($faker->sentence(4, true));
            $announce->setDescription($faker->paragraph);
            $announce->setPrice($faker->randomFloat(2, 0, 1000));
            $announce->setNumberOfBeds($faker->numberBetween(1, 10));
            $announce->setCreatedAt(new DateTime());
            $announce->setUpdatedAt(new DateTime());
            $announce->setHotel($faker->randomElement($hotel));
            $manager->persist($announce);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [HotelFixtures::class];
    }
}
