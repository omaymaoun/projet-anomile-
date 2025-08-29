<?php
// src/Controller/UserController.php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/users')]
class UserController extends AbstractController
{
    #[Route('/', name: 'user_list')]
    public function list(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(Users::class)->findAll();
        return $this->render('users/list.html.twig', compact('users'));
    }

   #[Route('/create', name: 'user_create')]
public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
{
    $user = new Users();
    $form = $this->createForm(UserType::class, $user);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $plainPassword = $form->get('plainPassword')->getData();
        $hashed = $hasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $em->persist($user);
        $em->flush();

        // Ajoute un message flash avant la redirection
        $this->addFlash('success', 'Utilisateur créé avec succès.');

      
    }

    return $this->render('users/form.html.twig', ['form' => $form->createView()]);
}


    #[Route('/edit/{id}', name: 'user_edit')]
    public function edit(Users $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashed = $hasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashed);
            }
            $em->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('user_list');
        }

        return $this->render('users/form.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/delete/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Users $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete-user' . $user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }
        return $this->redirectToRoute('user_list');
    }
}
