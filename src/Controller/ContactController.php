<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        // Affichage page
        if ($request->isMethod('GET')) {
            return $this->render('contact/index.html.twig');
        }

        // --- POST : traitement ---
        $title = trim((string) $request->request->get('title', ''));
        $message = trim((string) $request->request->get('message', ''));
        $email = trim((string) $request->request->get('email', ''));
        $token = (string) $request->request->get('_token', '');

        // CSRF
        if (!$this->isCsrfTokenValid('contact_message', $token)) {
            return $this->handleError($request, 'Token invalide. Recharge la page.');
        }

        // Validations simples
        if ($title === '' || mb_strlen($title) > 150) {
            return $this->handleError($request, 'Le titre est obligatoire (150 caractères max).');
        }
        if ($message === '') {
            return $this->handleError($request, 'Le message est obligatoire.');
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->handleError($request, 'Email invalide.');
        }

        // 1) Enregistrer en base
        $contact = new ContactMessage();
        $contact->setTitle($title);
        $contact->setMessage($message);
        $contact->setEmail($email);
        $contact->setCreatedAt(new \DateTimeImmutable());

        $em->persist($contact);
        $em->flush();

        // 2) Envoyer email
        // IMPORTANT: from doit être une adresse valide de ton serveur SMTP (souvent la même que dans le DSN)
        $from = 'service@sym-boost.com';

        try {
            $mail = (new Email())
                ->from($from)
                ->to('jaouna.ridouane@gmail.com')
                ->replyTo($email)
                ->subject('📩 Contact Vite&Gourmand : ' . $title)
                ->text(
                    "Nouveau message de contact\n\n" .
                    "De: {$email}\n" .
                    "Titre: {$title}\n\n" .
                    "Message:\n{$message}\n"
                );

            $mailer->send($mail);
        } catch (\Throwable $e) {
            // Message déjà enregistré en base, mais email KO
            return $this->handleError($request, "Message enregistré mais email non envoyé. Vérifie MAILER_DSN.");
        }

        // Succès
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['ok' => true, 'message' => 'Message envoyé ✅'], 200);
        }

        $this->addFlash('success', 'Message envoyé ✅');
        return $this->redirectToRoute('app_contact');
    }

    private function handleError(Request $request, string $msg): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['ok' => false, 'message' => $msg], 400);
        }

        $this->addFlash('danger', $msg);
        return $this->redirectToRoute('app_contact');
    }
}
