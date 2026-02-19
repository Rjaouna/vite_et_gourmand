<?php

namespace App\Controller\Admin;

use App\Entity\Dish;
use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/menus')]
class MenuController extends AbstractController
{
    private function getDishByType(Menu $menu, string $type): ?Dish
    {
        // si la relation n'existe pas (pas de getDishes), on évite de casser
        if (!method_exists($menu, 'getDishes')) {
            return null;
        }

        foreach ($menu->getDishes() as $d) {
            if ($d instanceof Dish && $d->getType() === $type) {
                return $d;
            }
        }
        return null;
    }

    private function syncMenuDishesFromForm(Menu $menu, FormInterface $form): void
    {
        // si la relation n'existe pas encore, on évite de casser
        if (!method_exists($menu, 'getDishes') || !method_exists($menu, 'addDish') || !method_exists($menu, 'removeDish')) {
            return;
        }

        // reset
        foreach ($menu->getDishes()->toArray() as $d) {
            $menu->removeDish($d);
        }

        foreach (['entreeDish', 'platDish', 'dessertDish'] as $field) {
            if (!$form->has($field)) {
                continue;
            }
            $dish = $form->get($field)->getData();
            if ($dish instanceof Dish) {
                $menu->addDish($dish);
            }
        }
    }

    #[Route('/', name: 'admin_menu_index', methods: ['GET'])]
    public function index(MenuRepository $repo): Response
    {
        return $this->render('admin/menu/index.html.twig', [
            'menus' => $repo->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_menu_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $menu = new Menu();
        $menu->setCreatedAt(new \DateTimeImmutable());
        $menu->setUpdatedAt(new \DateTimeImmutable());
        $menu->setIsActive(true);

        $form = $this->createForm(MenuType::class, $menu, [
            'action' => $this->generateUrl('admin_menu_new'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        // ✅ GET AJAX => HTML du form
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            return $this->render('admin/menu/_modal_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // ✅ POST AJAX => JSON
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {
                $menu->setUpdatedAt(new \DateTimeImmutable());

                // ✅ sauvegarde des plats optionnels
                $this->syncMenuDishesFromForm($menu, $form);

                $em->persist($menu);
                $em->flush();

                return new JsonResponse(['ok' => true, 'message' => 'Menu ajouté ✅']);
            }

            $html = $this->renderView('admin/menu/_modal_form.html.twig', [
                'form' => $form->createView(),
            ]);

            return new JsonResponse(['ok' => false, 'html' => $html], 422);
        }

        return $this->redirectToRoute('admin_menu_index');
    }

    #[Route('/{id}/edit', name: 'admin_menu_edit', methods: ['GET', 'POST'])]
    public function edit(Menu $menu, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MenuType::class, $menu, [
            'action' => $this->generateUrl('admin_menu_edit', ['id' => $menu->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        // ✅ GET AJAX => HTML du form (pré-remplissage des champs optionnels)
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            if ($form->has('entreeDish')) {
                $form->get('entreeDish')->setData($this->getDishByType($menu, Dish::TYPE_ENTREE));
            }
            if ($form->has('platDish')) {
                $form->get('platDish')->setData($this->getDishByType($menu, Dish::TYPE_PLAT));
            }
            if ($form->has('dessertDish')) {
                $form->get('dessertDish')->setData($this->getDishByType($menu, Dish::TYPE_DESSERT));
            }

            return $this->render('admin/menu/_modal_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // ✅ POST AJAX => JSON
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {
                $menu->setUpdatedAt(new \DateTimeImmutable());

                // ✅ important : sync des plats avant flush
                $this->syncMenuDishesFromForm($menu, $form);

                $em->flush();

                return new JsonResponse(['ok' => true, 'message' => 'Menu modifié ✅']);
            }

            $html = $this->renderView('admin/menu/_modal_form.html.twig', [
                'form' => $form->createView(),
            ]);

            return new JsonResponse(['ok' => false, 'html' => $html], 422);
        }

        return $this->redirectToRoute('admin_menu_index');
    }

    #[Route('/{id}', name: 'admin_menu_delete', methods: ['POST'])]
    public function delete(Menu $menu, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_menu_'.$menu->getId(), $request->request->get('_token'))) {
            $em->remove($menu);
            $em->flush();
            $this->addFlash('success', 'Menu supprimé ✅');
        }

        return $this->redirectToRoute('admin_menu_index');
    }
}
