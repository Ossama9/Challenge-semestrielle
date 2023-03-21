<?php

namespace App\DataFixtures;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Hotel;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(User::class)->findAll();
        $hotel = $manager->getRepository(Hotel::class)->findAll();
        $faker = Factory::create('fr_FR');
        $nbComments = 20;

        for ($i = 0; $i < $nbComments; $i++) {
            $comment = new Comment();
            $comment->setMessage($faker->paragraph);
            $comment->setNote($faker->numberBetween(0, 5));
            $comment->setUser($faker->randomElement($user));
            $comment->setHotel($faker->randomElement($hotel));
            $comment->setCreatedAt(new DateTime());
            $manager->persist($comment);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            HotelFixtures::class,
        ];
    }
}
