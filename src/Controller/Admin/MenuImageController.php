<?php

namespace App\Controller\Admin;

use App\Entity\Menu;
use App\Entity\MenuImage;
use App\Form\MenuImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/menus')]
class MenuImageController extends AbstractController
{
    #[Route('/{id}/images', name: 'admin_menu_images', methods: ['GET'])]
    public function index(Menu $menu): Response
    {
        return $this->render('admin/menu_image/index.html.twig', [
            'menu' => $menu,
            'images' => $menu->getImages(),
        ]);
    }

    #[Route('/{id}/images/new', name: 'admin_menu_image_new', methods: ['GET', 'POST'])]
    public function new(Menu $menu, Request $request, EntityManagerInterface $em): Response
    {
        $img = new MenuImage();
        $img->setMenu($menu);
        $img->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(MenuImageType::class, $img, [
            'action' => $this->generateUrl('admin_menu_image_new', ['id' => $menu->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        // GET AJAX => form HTML
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            return $this->render('admin/menu_image/_modal_form.html.twig', [
                'form' => $form->createView(),
                'menu' => $menu,
                'title' => 'Ajouter une image',
            ]);
        }

        // POST AJAX => JSON
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {
                $file = $form->get('file')->getData();
                if ($file) {
                    $safeName = uniqid('menu_', true) . '.' . $file->guessExtension();
                    $file->move($this->getParameter('menu_upload_dir'), $safeName);

                    // chemin web
                    $img->setImagePath('uploads/menus/' . $safeName);
                }

                // Si cover = true => on désactive les autres cover de ce menu
                if ($img->isCover()) {
                    foreach ($menu->getImages() as $other) {
                        $other->setIsCover(false);
                    }
                }

                $em->persist($img);
                $em->flush();

                return new JsonResponse(['ok' => true, 'message' => 'Image ajoutée ✅']);
            }

            $html = $this->renderView('admin/menu_image/_modal_form.html.twig', [
                'form' => $form->createView(),
                'menu' => $menu,
                'title' => 'Ajouter une image',
            ]);

            return new JsonResponse(['ok' => false, 'html' => $html], 422);
        }

        return $this->redirectToRoute('admin_menu_images', ['id' => $menu->getId()]);
    }

    #[Route('/images/{imgId}/delete', name: 'admin_menu_image_delete', methods: ['POST'])]
public function deleteImage(int $imgId, Request $request, EntityManagerInterface $em): JsonResponse
{
    $img = $em->getRepository(MenuImage::class)->find($imgId);
    if (!$img) {
        return $this->json(['ok' => false, 'message' => 'Image introuvable'], 404);
    }

    if (!$this->isCsrfTokenValid('delete_menu_image_'.$imgId, $request->request->get('_token'))) {
        return $this->json(['ok' => false, 'message' => 'Token CSRF invalide'], 403);
    }

    // supprimer le fichier physique (si existe)
    $path = $img->getImagePath(); // ex: uploads/menus/xxx.jpg
    if ($path) {
        $fullPath = $this->getParameter('kernel.project_dir').'/public/'.$path;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    $em->remove($img);
    $em->flush();

    return $this->json(['ok' => true, 'message' => 'Image supprimée ✅']);
}

#[Route('/images/{imgId}/cover', name: 'admin_menu_image_set_cover', methods: ['POST'])]
public function setCover(int $imgId, EntityManagerInterface $em): JsonResponse
{
    $img = $em->getRepository(MenuImage::class)->find($imgId);
    if (!$img) {
        return $this->json(['ok' => false, 'message' => 'Image introuvable'], 404);
    }

    $menu = $img->getMenu();

    // enlever cover des autres images
    foreach ($menu->getImages() as $other) {
        $other->setIsCover(false);
    }

    $img->setIsCover(true);
    $em->flush();

    return $this->json(['ok' => true, 'message' => 'Couverture mise à jour ✅']);
}

}
