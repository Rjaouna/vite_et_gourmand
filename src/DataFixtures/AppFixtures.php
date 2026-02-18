<?php

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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ) {}

    private function passwordFromEmail(string $email): string
    {
        // demandé : "début de mail + @123"
        $prefix = explode('@', $email)[0] ?: 'user';
        return $prefix . '@123';
    }

    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();

        // =========================
        // USERS (admin / employé / client)
        // =========================
        $usersData = [
            [
                'email' => 'admin@vitegourmand.fr',
                'roles' => ['ROLE_ADMIN'],
                'firstName' => 'José',
                'lastName' => 'Admin',
            ],
            [
                'email' => 'employe@vitegourmand.fr',
                'roles' => ['ROLE_EMPLOYEE'],
                'firstName' => 'Julie',
                'lastName' => 'Employée',
            ],
            [
                'email' => 'client@vitegourmand.fr',
                'roles' => [], // ROLE_USER est ajouté automatiquement dans getRoles()
                'firstName' => 'Client',
                'lastName' => 'Test',
            ],
        ];

        $users = [];

        foreach ($usersData as $u) {
            $user = new User();
            $user->setEmail($u['email']);
            $user->setRoles($u['roles']);
            $user->setFirstName($u['firstName']);
            $user->setLastName($u['lastName']);
            $user->setPhone('0600000000');
            $user->setAddressLine1('10 Rue du Test');
            $user->setCity('Bordeaux');
            $user->setPostalCode(33000);
            $user->setCountry('France');
            $user->setIsActive(true);
            $user->setCreatedAt($now);
            $user->setUpdatedAt($now);
            $user->setIsVerified(true);

            $plainPassword = $this->passwordFromEmail($u['email']); // ex: admin@123
            $user->setPassword($this->hasher->hashPassword($user, $plainPassword));

            $manager->persist($user);
            $users[] = $user;
        }

        // =========================
        // OPENING HOURS (7 jours)
        // =========================
        // 1=lundi ... 7=dimanche
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
            $oh = new OpeningHour();
            $oh->setDayOfWeek($day);
            $oh->setIsClosed($h['closed']);
            $oh->setCreatedAt($now);

            if (!$h['closed']) {
                $oh->setOpenTime(new \DateTimeImmutable($h['open']));
                $oh->setCloseTime(new \DateTimeImmutable($h['close']));
            }

            $manager->persist($oh);
        }

        // =========================
        // ALLERGENS
        // =========================
        $allergenNames = ['Gluten', 'Lactose', 'Arachides', 'Fruits à coque', 'Oeufs', 'Poisson', 'Soja'];
        foreach ($allergenNames as $name) {
            $a = new Allergen();
            $a->setName($name);
            $a->setCreatedAt($now);
            $manager->persist($a);
        }

        // =========================
        // DIETS
        // =========================
        $diets = [
            ['Végétarien', true],
            ['Vegan', true],
            ['Classique', true],
            ['Sans gluten', true],
        ];

        foreach ($diets as [$name, $active]) {
            $d = new Diet();
            $d->setName($name);
            $d->setIsActive($active);
            $d->setCreatedAt($now);
            $manager->persist($d);
        }

        // =========================
        // DISHES (entrée / plat / dessert)
        // =========================
        $dishesData = [
            ['Salade gourmande', 'entree', 'Salade fraîche avec toppings maison.'],
            ['Velouté de saison', 'entree', 'Velouté selon les légumes du moment.'],
            ['Poulet basquaise', 'plat', 'Poulet mijoté, sauce maison.'],
            ['Lasagnes végétariennes', 'plat', 'Lasagnes aux légumes et sauce tomate.'],
            ['Tarte aux pommes', 'dessert', 'Tarte croustillante, pommes caramélisées.'],
            ['Mousse au chocolat', 'dessert', 'Mousse légère, chocolat noir.'],
        ];

        foreach ($dishesData as [$name, $type, $desc]) {
            $dish = new Dish();
            $dish->setName($name);
            $dish->setType($type);
            $dish->setDescription($desc);
            $dish->setIsActive(true);
            $dish->setCreatedAt($now);
            $manager->persist($dish);
        }

        // =========================
        // MENUS + IMAGES (MenuImage lié à Menu)
        // =========================
        $menusData = [
            [
                'title' => 'Menu Classique',
                'theme' => 'Classique',
                'desc'  => 'Un menu simple et efficace, parfait pour tous les jours.',
                'conditions' => 'Commande minimum 48h à l’avance.',
                'minPeople' => 4,
                'minPrice'  => '45',
                'stock' => 10,
                'images' => [
                    ['assets/img/menus/classique_1.jpg', 'Menu classique', 1, true],
                    ['assets/img/menus/classique_2.jpg', 'Plat classique', 2, false],
                ],
            ],
            [
                'title' => 'Menu Noël',
                'theme' => 'Noël',
                'desc'  => 'Menu festif avec des saveurs de fin d’année.',
                'conditions' => 'Commande minimum 7 jours à l’avance.',
                'minPeople' => 6,
                'minPrice'  => '79',
                'stock' => 5,
                'images' => [
                    ['assets/img/menus/noel_1.jpg', 'Menu Noël', 1, true],
                ],
            ],
            [
                'title' => 'Menu Pâques',
                'theme' => 'Pâques',
                'desc'  => 'Menu spécial Pâques, gourmand et généreux.',
                'conditions' => 'Commande minimum 5 jours à l’avance.',
                'minPeople' => 6,
                'minPrice'  => '69',
                'stock' => 6,
                'images' => [
                    ['assets/img/menus/paques_1.jpg', 'Menu Pâques', 1, true],
                ],
            ],
            [
                'title' => 'Menu Évènement',
                'theme' => 'Évènement',
                'desc'  => 'Idéal pour anniversaires, réunions, petits évènements.',
                'conditions' => 'Commande minimum 72h à l’avance.',
                'minPeople' => 8,
                'minPrice'  => '120',
                'stock' => 3,
                'images' => [
                    ['assets/img/menus/event_1.jpg', 'Menu évènement', 1, true],
                ],
            ],
        ];

        foreach ($menusData as $m) {
            $menu = new Menu();
            $menu->setTitle($m['title']);
            $menu->setThemeLabel($m['theme']);
            $menu->setDescription($m['desc']);
            $menu->setConditions($m['conditions']);
            $menu->setMinPeople($m['minPeople']);
            $menu->setMinPrice($m['minPrice']); // DECIMAL stocké en string
            $menu->setStock($m['stock']);
            $menu->setIsActive(true);
            $menu->setCreatedAt($now);
            $menu->setUpdatedAt($now);

            // images liées
            foreach ($m['images'] as [$path, $alt, $pos, $cover]) {
                $img = new MenuImage();
                $img->setMenu($menu);
                $img->setImagePath($path);
                $img->setAltText($alt);
                $img->setPosition($pos);
                $img->setIsCover($cover);
                $img->setCreatedAt($now);

                // optionnel : grâce à cascade persist, persist menu suffit,
                // mais on peut persist image aussi sans souci
                $manager->persist($img);
                $menu->addImage($img);
            }

            $manager->persist($menu);
        }

        // =========================
        // CONTACT MESSAGES
        // =========================
        $contactSamples = [
            ['Demande devis', 'Bonjour, je souhaite un devis pour 12 personnes.', 'client@vitegourmand.fr'],
            ['Question menu', 'Pouvez-vous proposer une option végétarienne ?', 'test@mail.fr'],
        ];

        foreach ($contactSamples as [$title, $msg, $email]) {
            $cm = new ContactMessage();
            $cm->setTitle($title);
            $cm->setMessage($msg);
            $cm->setEmail($email);
            $cm->setCreatedAt($now);
            $manager->persist($cm);
        }

        $manager->flush();
    }
}
