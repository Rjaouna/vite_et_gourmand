<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Allergen;
use App\Entity\ContactMessage;
use App\Entity\Diet;
use App\Entity\Dish;
use App\Entity\Menu;
use App\Entity\MenuImage;
use App\Entity\OpeningHour;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    private function passwordFromEmail(string $email): string
    {
        $prefix = explode('@', $email)[0] ?: 'user';
        return $prefix . '@123';
    }

    private function createMenuPlaceholderImage(string $projectDir, string $fileName, string $title): string
    {
        $dir = $projectDir . '/public/uploads/menus';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $path = $dir . '/' . $fileName;

        $safeTitle = htmlspecialchars($title, ENT_QUOTES);
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800">
  <defs>
    <linearGradient id="g" x1="0" x2="1">
      <stop offset="0" stop-color="#f8f9fa"/>
      <stop offset="1" stop-color="#e9ecef"/>
    </linearGradient>
  </defs>
  <rect width="100%" height="100%" fill="url(#g)"/>
  <rect x="60" y="60" width="1080" height="680" rx="28" fill="#ffffff" stroke="#dee2e6" stroke-width="6"/>
  <text x="600" y="380" font-family="Arial, sans-serif" font-size="56" text-anchor="middle" fill="#212529">Vite &amp; Gourmand</text>
  <text x="600" y="470" font-family="Arial, sans-serif" font-size="40" text-anchor="middle" fill="#495057">{$safeTitle}</text>
</svg>
SVG;

        file_put_contents($path, $svg);

        return 'uploads/menus/' . $fileName;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $now = new \DateTimeImmutable();
        $projectDir = dirname(__DIR__, 2);

        // cover par défaut : public/uploads/menus/menu.png
        $defaultCoverPath = 'uploads/menus/menu.png';

        $menusDir = $projectDir . '/public/uploads/menus';
        if (!is_dir($menusDir)) {
            @mkdir($menusDir, 0775, true);
        }

        // =========================
        // USERS
        // =========================
        $admin = (new User())
            ->setEmail('admin@vitegourmand.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setFirstName('José')
            ->setLastName('Admin')
            ->setPhone('0600000000')
            ->setAddressLine1('10 Rue du Test')
            ->setCity('Bordeaux')
            ->setPostalCode(33000)
            ->setCountry('France')
            ->setIsActive(true)
            ->setIsVerified(true)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $admin->setPassword($this->hasher->hashPassword($admin, $this->passwordFromEmail($admin->getEmail())));
        $manager->persist($admin);

        $employee = (new User())
            ->setEmail('employe@vitegourmand.fr')
            ->setRoles(['ROLE_EMPLOYEE'])
            ->setFirstName('Julie')
            ->setLastName('Employée')
            ->setPhone('0600000001')
            ->setAddressLine1('12 Rue du Test')
            ->setCity('Bordeaux')
            ->setPostalCode(33000)
            ->setCountry('France')
            ->setIsActive(true)
            ->setIsVerified(true)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $employee->setPassword($this->hasher->hashPassword($employee, $this->passwordFromEmail($employee->getEmail())));
        $manager->persist($employee);

        for ($i = 0; $i < 20; $i++) {
            $email = $faker->unique()->safeEmail();

            $u = (new User())
                ->setEmail($email)
                ->setRoles([])
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setPhone('06' . $faker->numerify('########'))
                ->setAddressLine1($faker->streetAddress())
                ->setCity($faker->city())
                ->setPostalCode((int) $faker->postcode())
                ->setCountry('France')
                ->setIsActive(true)
                ->setIsVerified(true)
                ->setCreatedAt($now)
                ->setUpdatedAt($now);

            $u->setPassword($this->hasher->hashPassword($u, $this->passwordFromEmail($email)));
            $manager->persist($u);
        }

        // =========================
        // OPENING HOURS
        // =========================
        $hours = [
            1 => ['closed' => false, 'open' => '09:00', 'close' => '18:00'],
            2 => ['closed' => false, 'open' => '09:00', 'close' => '18:00'],
            3 => ['closed' => false, 'open' => '09:00', 'close' => '18:00'],
            4 => ['closed' => false, 'open' => '09:00', 'close' => '18:00'],
            5 => ['closed' => false, 'open' => '09:00', 'close' => '18:00'],
            6 => ['closed' => false, 'open' => '10:00', 'close' => '16:00'],
            7 => ['closed' => true,  'open' => null,   'close' => null],
        ];

        foreach ($hours as $day => $h) {
            $oh = (new OpeningHour())
                ->setDayOfWeek($day)
                ->setIsClosed($h['closed'])
                ->setCreatedAt($now);

            if (!$h['closed']) {
                $oh->setOpenTime(new \DateTimeImmutable($h['open']));
                $oh->setCloseTime(new \DateTimeImmutable($h['close']));
            }
            $manager->persist($oh);
        }

        // =========================
        // ALLERGENS
        // =========================
        $allergenNames = ['Gluten', 'Lactose', 'Arachides', 'Fruits à coque', 'Oeufs', 'Poisson', 'Soja', 'Sésame', 'Moutarde'];
        foreach ($allergenNames as $name) {
            $a = (new Allergen())
                ->setName($name)
                ->setCreatedAt($now);
            $manager->persist($a);
        }

        // =========================
        // DIETS
        // =========================
        $dietNames = ['Végétarien', 'Vegan', 'Classique', 'Sans gluten', 'Halal'];
        foreach ($dietNames as $name) {
            $d = (new Diet())
                ->setName($name)
                ->setIsActive(true)
                ->setCreatedAt($now);
            $manager->persist($d);
        }

        // =========================
        // DISHES
        // =========================
        $entrees = [];
        $plats = [];
        $desserts = [];

        for ($i = 0; $i < 25; $i++) {
            $dish = (new Dish())
                ->setName('Entrée ' . ucfirst($faker->words(2, true)))
                ->setType(Dish::TYPE_ENTREE)
                ->setDescription($faker->sentence(14))
                ->setIsActive(true)
                ->setCreatedAt($now);

            $manager->persist($dish);
            $entrees[] = $dish;
        }

        for ($i = 0; $i < 35; $i++) {
            $dish = (new Dish())
                ->setName('Plat ' . ucfirst($faker->words(2, true)))
                ->setType(Dish::TYPE_PLAT)
                ->setDescription($faker->sentence(16))
                ->setIsActive(true)
                ->setCreatedAt($now);

            $manager->persist($dish);
            $plats[] = $dish;
        }

        for ($i = 0; $i < 25; $i++) {
            $dish = (new Dish())
                ->setName('Dessert ' . ucfirst($faker->words(2, true)))
                ->setType(Dish::TYPE_DESSERT)
                ->setDescription($faker->sentence(12))
                ->setIsActive(true)
                ->setCreatedAt($now);

            $manager->persist($dish);
            $desserts[] = $dish;
        }

        // =========================
        // MENUS + IMAGES + COMPOSITION OPTIONNELLE
        // =========================
        $themes = ['Classique', 'Noël', 'Pâques', 'Évènement', 'Anniversaire', 'Buffet', 'Cocktail'];
        $menuCount = 18;

        for ($i = 1; $i <= $menuCount; $i++) {
            $menu = (new Menu())
                ->setTitle('Menu ' . ucfirst($faker->words(2, true)))
                ->setThemeLabel($faker->randomElement($themes))
                ->setDescription($faker->sentence(18))
                ->setConditions('Commande minimum ' . $faker->numberBetween(24, 168) . 'h à l’avance.')
                ->setMinPeople($faker->numberBetween(2, 12))
                ->setMinPrice((string) $faker->numberBetween(10, 28))
                ->setStock($faker->numberBetween(0, 15))
                ->setIsActive(true)
                ->setCreatedAt($now)
                ->setUpdatedAt($now);

            // ✅ Composition (optionnelle)
            if (method_exists($menu, 'addDish')) {
                // entrée 90% du temps
                if ($faker->boolean(90) && !empty($entrees)) {
                    $menu->addDish($faker->randomElement($entrees));
                }
                // plat 95% du temps
                if ($faker->boolean(95) && !empty($plats)) {
                    $menu->addDish($faker->randomElement($plats));
                }
                // dessert 70% du temps
                if ($faker->boolean(70) && !empty($desserts)) {
                    $menu->addDish($faker->randomElement($desserts));
                }
            }

            // Images
            $imgCount = $faker->numberBetween(1, 4);
            for ($j = 1; $j <= $imgCount; $j++) {
                $isCover = ($j === 1);

                $imgPath = $isCover
                    ? $defaultCoverPath
                    : $this->createMenuPlaceholderImage($projectDir, 'menu_'.$i.'_'.$j.'.svg', $menu->getTitle());

                $img = (new MenuImage())
                    ->setMenu($menu)
                    ->setImagePath($imgPath)
                    ->setAltText($menu->getTitle() . ' - image ' . $j)
                    ->setPosition($j)
                    ->setIsCover($isCover)
                    ->setCreatedAt($now);

                $menu->addImage($img);
                $manager->persist($img);
            }

            $manager->persist($menu);
        }

        // =========================
        // CONTACT MESSAGES
        // =========================
        for ($i = 0; $i < 30; $i++) {
            $cm = (new ContactMessage())
                ->setTitle(ucfirst($faker->words(4, true)))
                ->setMessage($faker->paragraphs(2, true))
                ->setEmail($faker->safeEmail())
                ->setCreatedAt($now);

            $manager->persist($cm);
        }

        $manager->flush();
    }
}
