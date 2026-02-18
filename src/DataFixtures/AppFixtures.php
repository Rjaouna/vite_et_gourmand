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

    /**
     * Crée un faux fichier image (SVG) dans /public/uploads/menus/
     * et retourne le chemin relatif utilisable par asset().
     */
    private function createMenuPlaceholderImage(string $projectDir, string $fileName, string $title): string
    {
        $dir = $projectDir . '/public/uploads/menus';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $path = $dir . '/' . $fileName;

        // petit SVG simple (pas besoin de lib)
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

        // chemin relatif (public/..)
        return 'uploads/menus/' . $fileName;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $now   = new \DateTimeImmutable();
        $projectDir = dirname(__DIR__, 2);

        // ✅ Image principale par défaut pour chaque menu
        // correspond à : public/uploads/menus/menu.png
        $defaultCoverPath = 'uploads/menus/menu.png';

        // s'assure que le dossier existe (le fichier menu.png doit exister côté projet)
        $menusDir = $projectDir . '/public/uploads/menus';
        if (!is_dir($menusDir)) {
            @mkdir($menusDir, 0775, true);
        }

        // =========================
        // USERS (admin / employé / clients)
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

        $clients = [];
        for ($i = 0; $i < 20; $i++) {
            $email = $faker->unique()->safeEmail();

            $u = (new User())
                ->setEmail($email)
                ->setRoles([]) // ROLE_USER auto dans getRoles()
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
            $clients[] = $u;
        }

        // =========================
        // OPENING HOURS (7 jours)
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
        $allergens = [];
        foreach ($allergenNames as $name) {
            $a = (new Allergen())
                ->setName($name)
                ->setCreatedAt($now);
            $manager->persist($a);
            $allergens[] = $a;
        }

        // =========================
        // DIETS
        // =========================
        $dietNames = ['Végétarien', 'Vegan', 'Classique', 'Sans gluten', 'Halal'];
        $diets = [];
        foreach ($dietNames as $name) {
            $d = (new Diet())
                ->setName($name)
                ->setIsActive(true)
                ->setCreatedAt($now);
            $manager->persist($d);
            $diets[] = $d;
        }

        // =========================
        // DISHES (beaucoup)
        // =========================
        $dishTypes = ['entree', 'plat', 'dessert'];
        $dishes = [];
        for ($i = 0; $i < 40; $i++) {
            $type = $faker->randomElement($dishTypes);

            $dish = (new Dish())
                ->setName(ucfirst($faker->words(3, true)))
                ->setType($type)
                ->setDescription($faker->sentence(12))
                ->setIsActive(true)
                ->setCreatedAt($now);

            $manager->persist($dish);
            $dishes[] = $dish;
        }

        // =========================
        // MENUS + IMAGES
        // =========================
        $themes = ['Classique', 'Noël', 'Pâques', 'Évènement', 'Anniversaire', 'Buffet', 'Cocktail'];
        $menuCount = 18;

        for ($i = 1; $i <= $menuCount; $i++) {
            $theme = $faker->randomElement($themes);
            $minPeople = $faker->numberBetween(2, 12);
            $minPrice = $faker->numberBetween(35, 160); // on stocke en string

            $menu = (new Menu())
                ->setTitle('Menu ' . ucfirst($faker->words(2, true)))
                ->setThemeLabel($theme)
                ->setDescription($faker->sentence(18))
                ->setConditions('Commande minimum ' . $faker->numberBetween(24, 168) . 'h à l’avance.')
                ->setMinPeople($minPeople)
                ->setMinPrice((string) $minPrice)
                ->setStock($faker->numberBetween(0, 15))
                ->setIsActive(true)
                ->setCreatedAt($now)
                ->setUpdatedAt($now);

            // 1 à 4 images par menu
            $imgCount = $faker->numberBetween(1, 4);
            for ($j = 1; $j <= $imgCount; $j++) {
                $isCover = ($j === 1); // la première cover

                if ($isCover) {
                    // ✅ Photo principale par défaut (la même pour tous les menus)
                    $imgPath = $defaultCoverPath;
                } else {
                    // Images secondaires : placeholders SVG générés
                    $fileName = 'menu_' . $i . '_' . $j . '.svg';
                    $imgPath = $this->createMenuPlaceholderImage($projectDir, $fileName, $menu->getTitle());
                }

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
        // CONTACT MESSAGES (beaucoup)
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
