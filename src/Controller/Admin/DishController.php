<?php

namespace App\Controller\Admin;

use App\Entity\Dish;
use App\Form\DishType;
use App\Repository\DishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/dishes')]
class DishController extends AbstractController
{
    #[Route('/', name: 'admin_dish_index', methods: ['GET'])]
    public function index(DishRepository $repo): Response
    {
        return $this->render('admin/dish/index.html.twig', [
            'dishes' => $repo->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_dish_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $dish = new Dish();
        $dish->setCreatedAt(new \DateTimeImmutable());
        $dish->setIsActive(true);

        $form = $this->createForm(DishType::class, $dish, [
            'action' => $this->generateUrl('admin_dish_new'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        // GET AJAX => HTML form
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            return $this->render('admin/dish/_modal_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // POST AJAX => JSON
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($dish);
                $em->flush();

                return new JsonResponse(['ok' => true, 'message' => 'Plat ajouté ✅']);
            }

            $html = $this->renderView('admin/dish/_modal_form.html.twig', [
                'form' => $form->createView(),
            ]);

            return new JsonResponse(['ok' => false, 'html' => $html], 422);
        }

        return $this->redirectToRoute('admin_dish_index');
    }

    #[Route('/{id}/edit', name: 'admin_dish_edit', methods: ['GET', 'POST'])]
    public function edit(Dish $dish, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DishType::class, $dish, [
            'action' => $this->generateUrl('admin_dish_edit', ['id' => $dish->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        // GET AJAX => HTML form
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            return $this->render('admin/dish/_modal_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // POST AJAX => JSON
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();
                return new JsonResponse(['ok' => true, 'message' => 'Plat modifié ✅']);
            }

            $html = $this->renderView('admin/dish/_modal_form.html.twig', [
                'form' => $form->createView(),
            ]);

            return new JsonResponse(['ok' => false, 'html' => $html], 422);
        }

        return $this->redirectToRoute('admin_dish_index');
    }

    #[Route('/{id}', name: 'admin_dish_delete', methods: ['POST'])]
    public function delete(Dish $dish, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_dish_'.$dish->getId(), $request->request->get('_token'))) {
            $em->remove($dish);
            $em->flush();
            $this->addFlash('success', 'Plat supprimé ✅');
        }

        return $this->redirectToRoute('admin_dish_index');
    }
}
