<?php
// src/Controller/CreateUserController.php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Technicien;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserController extends AbstractController
{
    #[Route('/create-user', name: 'create_user')]
    public function addUser(UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        // 1. Création de l'utilisateur
        $user = new Users();
        $user->setUsername('jalel');
        $hashed = $hasher->hashPassword($user, 'MonPass123!');
        $user->setPassword($hashed);
        $user->setRoles(['ROLE_TECHNICIEN']);

    

        // 3. Sauvegarde
        $em->persist($user);
        $em->persist($technicien);
        $em->flush();

        return new Response("✅ Technicien avec utilisateur créé avec succès !");
    }
}

