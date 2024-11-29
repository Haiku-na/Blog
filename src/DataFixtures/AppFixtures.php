<?php
// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer une instance de Faker
        $faker = Factory::create();

        // Création de 5 catégories
        $categories = [];
        for ($i = 0; $i < 5; $i++) {
            $category = new Category();
            $category->setName($faker->word())
                     ->setDescription($faker->sentence());
            $manager->persist($category);
            $categories[] = $category;
        }

        // Création de 3 utilisateurs
        $users = [];
        for ($i = 0; $i < 3; $i++) {
            $user = new User();
            $user->setEmail($faker->email())
                 ->setPassword($faker->password())
                 ->setFirstName($faker->firstName())
                 ->setLastName($faker->lastName())
                 ->setProfilePicture($faker->imageUrl())
                 ->setCreatedAt($faker->dateTimeThisYear())
                 ->setUpdatedAt($faker->dateTimeThisYear());
            $manager->persist($user);
            $users[] = $user;
        }

        // Création de 10 posts avec des catégories et des utilisateurs
        for ($i = 0; $i < 10; $i++) {
            $post = new Post();
            $post->setTitle($faker->sentence())
                 ->setContent($faker->text())
                 ->setPublishedAt($faker->dateTimeThisYear())
                 ->setPicture($faker->imageUrl())
                 ->setUser($faker->randomElement($users))  // Affecter un utilisateur aléatoire
                 ->setCategory($faker->randomElement($categories));  // Affecter une catégorie aléatoire
            $manager->persist($post);
        }

        // Création de 20 commentaires pour les posts
        for ($i = 0; $i < 20; $i++) {
            $comment = new Comment();
            $comment->setContent($faker->sentence())
                    ->setCreateAt($faker->dateTimeThisYear())
                    ->setStatus($faker->randomElement(['validé', 'rejeté', 'en attente'])) // Status aléatoire
                    ->setUser($faker->randomElement($users))  // Affecter un utilisateur aléatoire
                    ->setPost($faker->randomElement($manager->getRepository(Post::class)->findAll())); // Affecter un post existant
            $manager->persist($comment);
        }

        // Enregistrer toutes les entités dans la base de données
        $manager->flush();
    }
}
