<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Payment;
use App\Entity\Reservation;
use App\Entity\User;
use Faker\Factory;

class PaymentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $reservations = $manager->getRepository(Reservation::class)->findAll();

        foreach ($reservations as $reservation) {
            $payment = new Payment();
            $payment->setReservation($reservation);
            $payment->setUser($reservation->getUser());
            $payment->setAmount($reservation->getPrice());
            $payment->setCreatedAt($reservation->getCreatedAt());
            $manager->persist($payment);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ReservationFixtures::class,
            UserFixtures::class,
        ];
    }
}
