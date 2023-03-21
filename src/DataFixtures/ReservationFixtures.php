<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Reservation;
use App\Entity\Announce;
use App\Entity\User;
use Faker\Factory;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $users = $manager->getRepository(User::class)->findAll();
        $announces = $manager->getRepository(Announce::class)->findAll();
        $nbReservations = 5;

        for ($i = 0; $i < $nbReservations; $i++) {
            $reservation = new Reservation();
            $reservation->setUser($faker->randomElement($users));
            $reservation->setAnnounce($faker->randomElement($announces));
            $reservation->setStart($faker->dateTimeBetween('-1 years', 'now'));
            $reservation->setEnd($faker->dateTimeBetween('now', '+1 years'));
            $reservation->setCreatedAt($faker->dateTimeBetween('-1 years', 'now'));
            $reservation->setUpdatedAt($faker->dateTimeBetween('-1 years', 'now'));
            $reservation->setPrice($faker->randomFloat(2, 0, 1000));
            $reservation->setName($faker->name);
            $manager->persist($reservation);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            AnnounceFixtures::class,
        ];
    }
}
