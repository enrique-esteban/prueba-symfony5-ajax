<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;

class CategoriesFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
    
        $category = new Category();
        $category->setName('php');
        $manager->persist($category);

        $category = new Category();
        $category->setName('javascript');
        $manager->persist($category);

        $category = new Category();
        $category->setName('css');
        $manager->persist($category);

        $manager->flush();
    }
}
