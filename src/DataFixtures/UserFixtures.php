<?php

namespace App\DataFixtures;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // password: test
        $password = '$2y$13$YZjsXWaXo828Z/FPeUBw4ehot8ztPX/rk6VS1m4Wv3vpc4twCEF/O';
        $faker = Factory::create('fr_FR');
        $nbUsers = 5;
        $nbCustomers = 5;
        $nbFutureCustomers = 5;

        $admin = new User();
        $admin->setEmail('admin@aze.fr');
        $admin->setPassword($password);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setToken(bin2hex(random_bytes(64)));
        $admin->setIsActivated(true);
        $admin->setFirstname($faker->firstName);
        $admin->setLastname($faker->lastName);
        $admin->setLastLogin(new DateTime());
        $admin->setCreatedAt(new DateTime());
        $admin->setUpdatedAt(new DateTime());
        $admin->setAvatar('default.png');
        $manager->persist($admin);

        for ($i = 0; $i < $nbUsers; $i++) {
            $user = new User();
            $user->setEmail('user'.$i.'@aze.fr');
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);
            $user->setToken(bin2hex(random_bytes(64)));
            $user->setIsActivated(true);
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setLastLogin(new DateTime());
            $user->setCreatedAt(new DateTime());
            $user->setUpdatedAt(new DateTime());
            $user->setAvatar('default.png');
            $manager->persist($user);
        }

        for ($i = 0; $i < $nbFutureCustomers; $i++) {
            $user = new User();
            $user->setEmail('future-customer'.$i.'@aze.fr');
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);
            $user->setIsCustomer(0);
            $user->setToken(bin2hex(random_bytes(64)));
            $user->setIsActivated(true);
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setLastLogin(new DateTime());
            $user->setCreatedAt(new DateTime());
            $user->setUpdatedAt(new DateTime());
            $user->setAvatar('default.png');
            $manager->persist($user);
        }

        for ($i = 0; $i < $nbCustomers; $i++) {
            $customer = new User();
            $customer->setEmail('customer'.$i.'@aze.fr');
            $customer->setPassword($password);
            $customer->setRoles(['ROLE_CUSTOMER']);
            $customer->setToken(bin2hex(random_bytes(64)));
            $customer->setIsActivated(true);
            $customer->setIsCustomer(1);
            $customer->setFirstname($faker->firstName);
            $customer->setLastname($faker->lastName);
            $customer->setLastLogin(new DateTime());
            $customer->setCreatedAt(new DateTime());
            $customer->setUpdatedAt(new DateTime());
            $customer->setAvatar('default.png');
            $manager->persist($customer);
        }

        $manager->flush();
    }
}
