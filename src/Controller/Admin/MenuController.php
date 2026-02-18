<?php

namespace App\Controller\Admin;

use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/menus')]
class MenuController extends AbstractController
{
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

        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $menu->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($menu);
            $em->flush();

            $this->addFlash('success', 'Menu créé ✅');
            return $this->redirectToRoute('admin_menu_index');
        }

        return $this->render('admin/menu/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_menu_edit', methods: ['GET', 'POST'])]
    public function edit(Menu $menu, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $menu->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Menu modifié ✅');
            return $this->redirectToRoute('admin_menu_index');
        }

        return $this->render('admin/menu/edit.html.twig', [
            'form' => $form->createView(),
            'menu' => $menu,
        ]);
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
