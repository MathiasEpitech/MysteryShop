<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Categorie;
use App\Entity\Produitmf;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{

    protected $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');

        for ($j = 0; $j <= 4; $j++) {

            $categorie = new Categorie();

            $categorie->setNom($faker->sentence())
                      ->setSlug($this->slugger->slug( $categorie->getNom() ) );

                $manager->persist($categorie);

                for ($i = 1; $i <= mt_rand(5, 30); $i++) {

                    $produit = new Produitmf();

                    $produit->setNom("Produit n° $i")
                        ->setImage("https://picsum.photos/id/" . mt_rand(12, 250) . "/300/160")
                        ->setPrix(mt_rand(10, 89))
                        ->setStock(mt_rand(10, 100))
                        ->setDescription("Voici la description du produit n° $i")
                        ->setCategorie($categorie);

                    $manager->persist($produit);
                }

        }

        $manager->flush();
    }

}
