<?php

namespace App\Controller\Admin;

use App\Entity\OpeningHour;
use App\Form\OpeningHourType;
use App\Repository\OpeningHourRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/opening-hours')]
class OpeningHourController extends AbstractController
{
    #[Route('/', name: 'admin_opening_hour_index', methods: ['GET'])]
    public function index(OpeningHourRepository $repo): Response
    {
        $hours = $repo->findBy([], ['dayOfWeek' => 'ASC']);

        return $this->render('admin/opening_hour/index.html.twig', [
            'hours' => $hours,
            'days'  => self::days(),
        ]);
    }

    #[Route('/new', name: 'admin_opening_hour_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $openingHour = new OpeningHour();
        $openingHour->setCreatedAt(new \DateTimeImmutable());
        $openingHour->setIsClosed(false);

        // ✅ IMPORTANT : action forcée => le POST ne part JAMAIS sur /admin/opening-hours/
        $form = $this->createForm(OpeningHourType::class, $openingHour, [
            'days'   => self::days(),
            'action' => $this->generateUrl('admin_opening_hour_new'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        // GET AJAX => HTML du formulaire (modal)
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            return $this->render('admin/opening_hour/_modal_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // POST AJAX => JSON
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {
                if ($openingHour->isClosed()) {
                    $openingHour->setOpenTime(null);
                    $openingHour->setCloseTime(null);
                }

                $em->persist($openingHour);
                $em->flush();

                return new JsonResponse(['ok' => true, 'message' => 'Horaire ajouté ✅']);
            }

            $html = $this->renderView('admin/opening_hour/_modal_form.html.twig', [
                'form' => $form->createView(),
            ]);

            return new JsonResponse(['ok' => false, 'html' => $html], 422);
        }

        // On n'utilise pas /new en page normale => retour index
        return $this->redirectToRoute('admin_opening_hour_index');
    }

    #[Route('/{id}/edit', name: 'admin_opening_hour_edit', methods: ['GET', 'POST'])]
    public function edit(OpeningHour $openingHour, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(OpeningHourType::class, $openingHour, [
            'days'   => self::days(),
            'action' => $this->generateUrl('admin_opening_hour_edit', ['id' => $openingHour->getId()]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        // GET AJAX => HTML du formulaire
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            return $this->render('admin/opening_hour/_modal_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // POST AJAX => JSON
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {
                if ($openingHour->isClosed()) {
                    $openingHour->setOpenTime(null);
                    $openingHour->setCloseTime(null);
                }

                $em->flush();

                return new JsonResponse(['ok' => true, 'message' => 'Horaire modifié ✅']);
            }

            $html = $this->renderView('admin/opening_hour/_modal_form.html.twig', [
                'form' => $form->createView(),
            ]);

            return new JsonResponse(['ok' => false, 'html' => $html], 422);
        }

        return $this->redirectToRoute('admin_opening_hour_index');
    }

    #[Route('/{id}', name: 'admin_opening_hour_delete', methods: ['POST'])]
    public function delete(OpeningHour $openingHour, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_opening_hour_'.$openingHour->getId(), $request->request->get('_token'))) {
            $em->remove($openingHour);
            $em->flush();
            $this->addFlash('success', 'Horaire supprimé ✅');
        }

        return $this->redirectToRoute('admin_opening_hour_index');
    }

    private static function days(): array
    {
        return [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
        ];
    }
}
