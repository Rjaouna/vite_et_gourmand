<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('profile/index.html.twig');
    }

    #[Route('/profile/update-field', name: 'app_profile_update_field', methods: ['POST'])]
    public function updateField(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['ok' => false, 'message' => 'Requête invalide'], 400);
        }

        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true) ?? [];
        $field = $data['field'] ?? null;
        $value = $data['value'] ?? null;

        $allowed = [
            'firstName', 'lastName', 'phone',
            'addressLine1', 'addressLine2',
            'postalCode', 'city', 'country'
        ];

        if (!$field || !in_array($field, $allowed, true)) {
            return new JsonResponse(['ok' => false, 'message' => 'Champ non autorisé'], 400);
        }

        // Cast/normalisation simple
        if ($field === 'postalCode') {
            $value = ($value === '' || $value === null) ? null : (int) $value;
        } else {
            $value = is_string($value) ? trim($value) : $value;
            if ($value === '') $value = null;
        }

        // Setters
        match ($field) {
            'firstName' => $user->setFirstName($value),
            'lastName' => $user->setLastName($value),
            'phone' => $user->setPhone($value),
            'addressLine1' => $user->setAddressLine1($value),
            'addressLine2' => $user->setAddressLine2($value),
            'postalCode' => $user->setPostalCode($value),
            'city' => $user->setCity($value),
            'country' => $user->setCountry($value),
        };

        // optionnel : updatedAt
        $user->setUpdatedAt(new \DateTimeImmutable());

        // Validation entity (si tu as des contraintes, sinon ça passe)
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            // on renvoie le 1er message (simple)
            return new JsonResponse(['ok' => false, 'message' => $errors[0]->getMessage()], 422);
        }

        $em->flush();

        return new JsonResponse([
            'ok' => true,
            'message' => 'Enregistré ✅',
            'field' => $field,
            'value' => $value,
        ]);
    }
}
