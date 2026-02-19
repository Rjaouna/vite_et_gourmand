<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MenuPublicController extends AbstractController
{
    #[Route('/menus', name: 'app_menus', methods: ['GET'])]
    public function index(MenuRepository $repo): Response
    {
        // pour alimenter le select "Thème" (unique)
        $themes = $repo->createQueryBuilder('m')
            ->select('DISTINCT m.themeLabel')
            ->where('m.isActive = true')
            ->andWhere('m.themeLabel IS NOT NULL')
            ->orderBy('m.themeLabel', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        // listing initial (sans filtres)
        $menus = $repo->findBy(['isActive' => true], ['id' => 'DESC']);

        return $this->render('menu_public/index.html.twig', [
            'menus' => $menus,
            'themes' => $themes,
        ]);
    }

    #[Route('/menus/search', name: 'app_menus_search', methods: ['GET'])]
    public function search(Request $request, MenuRepository $repo): JsonResponse
    {
        $qb = $repo->createQueryBuilder('m')
            ->where('m.isActive = true');

        // filtres
        $priceMax = $request->query->get('priceMax');     // ex: 50
        $priceMin = $request->query->get('priceMin');     // ex: 20
        $priceMaxRange = $request->query->get('priceMaxRange'); // ex: 80
        $theme = $request->query->get('theme');           // ex: Noel
        $minPeople = $request->query->get('minPeople');   // ex: 6

        // Prix max simple
        if ($priceMax !== null && $priceMax !== '') {
            $qb->andWhere('m.minPrice <= :priceMax')
               ->setParameter('priceMax', (float) $priceMax);
        }

        // Fourchette prix (min/max)
        if ($priceMin !== null && $priceMin !== '') {
            $qb->andWhere('m.minPrice >= :priceMin')
               ->setParameter('priceMin', (float) $priceMin);
        }
        if ($priceMaxRange !== null && $priceMaxRange !== '') {
            $qb->andWhere('m.minPrice <= :priceMaxRange')
               ->setParameter('priceMaxRange', (float) $priceMaxRange);
        }

        // Thème
        if ($theme !== null && $theme !== '') {
            $qb->andWhere('m.themeLabel = :theme')
               ->setParameter('theme', $theme);
        }

        // Nombre de personnes minimum (le client veut un menu pour au moins X personnes)
        if ($minPeople !== null && $minPeople !== '') {
            $qb->andWhere('m.minPeople <= :minPeople')
               ->setParameter('minPeople', (int) $minPeople);
        }

        $qb->orderBy('m.id', 'DESC');

        $menus = $qb->getQuery()->getResult();

        // rendre HTML via partial (simple)
        $html = $this->renderView('menu_public/_cards.html.twig', [
            'menus' => $menus,
        ]);

        return new JsonResponse([
            'ok' => true,
            'count' => count($menus),
            'html' => $html,
        ]);
    }

    #[Route('/menu/{id}', name: 'menu_show', requirements: ['id' => '\d+'])]
    public function show(Menu $menu): Response
    {
        // ✅ Image cover par défaut
        $defaultCover = 'uploads/menus/menu.png';

        $coverPath = $defaultCover;
        $sideImagePath = $defaultCover;

        // Cover = image marquée isCover
        foreach ($menu->getImages() as $img) {
            if (method_exists($img, 'isCover') && $img->isCover()) {
                $coverPath = $img->getImagePath() ?: $defaultCover;
                break;
            }
        }

        // Petite image à droite = première image non cover (sinon cover)
        foreach ($menu->getImages() as $img) {
            if (method_exists($img, 'isCover') && !$img->isCover()) {
                $sideImagePath = $img->getImagePath() ?: $coverPath;
                break;
            }
        }
        if (!$sideImagePath) {
            $sideImagePath = $coverPath;
        }

        /**
         * ⚠️ Entrée/Plat/Dessert :
         * Ton entity Menu ne montre pas de relation vers des plats.
         * Donc je mets une structure "courses" prête à brancher plus tard.
         * Pour l’instant : vide => le template affiche un contenu fallback.
         */
        $courses = [
            'entree' => [],
            'plat' => [],
            'dessert' => [],
        ];

        return $this->render('menu/show.html.twig', [
            'menu' => $menu,
            'coverPath' => $coverPath,
            'sideImagePath' => $sideImagePath,
            'courses' => $courses,
        ]);
    }
}
