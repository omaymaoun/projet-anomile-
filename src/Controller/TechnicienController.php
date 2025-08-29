<?php

namespace App\Controller;

use App\Entity\Technicien;
use App\Form\TechnicienType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Ticket; 
use App\Repository\TicketRepository;
use Symfony\Component\Security\Core\Security;
use App\Entity\Users;





#[Route('/technicien')]
final class TechnicienController extends AbstractController
{
    #[Route(name: 'app_technicien_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ticketId = $request->query->get('ticketId'); // Récupère le ticketId dans l'URL ?ticketId=...

        $techniciens = $entityManager->getRepository(Technicien::class)->findAll();

        return $this->render('technicien/index.html.twig', [
            'techniciens' => $techniciens,
            'ticketId' => $ticketId,
        ]);
    }

   #[Route('/technicien/new/{id}', name: 'app_technicien_new')]
public function new(Request $request, EntityManagerInterface $em, Users $user): Response
{
    $technicien = new Technicien();

    // Associer l'utilisateur au technicien
    $technicien->setUser($user);

    // Créer et gérer le formulaire comme d'habitude
    $form = $this->createForm(TechnicienType::class, $technicien);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($technicien);

        // Ajouter rôle technicien si besoin
        $roles = $user->getRoles();
        if (!in_array('ROLE_TECHNICIEN', $roles)) {
            $roles[] = 'ROLE_TECHNICIEN';
            $user->setRoles($roles);
            $em->persist($user);
        }

        $em->flush();

        $this->addFlash('success', 'Technicien créé avec succès.');

        return $this->redirectToRoute('user_list');
    }

    return $this->render('technicien/new.html.twig', [
        'form' => $form->createView(),
        'user' => $user,
    ]);
}




    #[Route('/{id}', name: 'app_technicien_show', methods: ['GET'])]
    public function show(Technicien $technicien): Response
    {
        return $this->render('technicien/show.html.twig', [
            'technicien' => $technicien,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_technicien_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Technicien $technicien, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TechnicienType::class, $technicien);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_technicien_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('technicien/edit.html.twig', [
            'technicien' => $technicien,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_technicien_delete', methods: ['POST'])]
    public function delete(Request $request, Technicien $technicien, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$technicien->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($technicien);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_technicien_index', [], Response::HTTP_SEE_OTHER);
    }



 #[Route('/technicien/mes-tickets', name: 'app_technicien_mes_tickets')]
 
public function mesTickets(TicketRepository $ticketRepository, Security $security): Response
{
    /** @var Users|null $user */
    $user = $security->getUser();

    if (!$user) {
        throw $this->createAccessDeniedException('Vous devez être connecté.');
    }

    $technicien = $user->getTechnicien();


   
    // Récupère uniquement les tickets liés au technicien connecté
    $tickets = $ticketRepository->findBy(['technicien' => $technicien]);

    return $this->render('technicien/mes_tickets.html.twig', [
        'tickets' => $tickets,
    ]);
}




 #[Route('/technicien/create-from-user/{id}', name: 'technicien_create_from_user')]
    public function createFromUser(Users $user, EntityManagerInterface $em): Response
    {
        // Vérifier si un technicien existe déjà pour cet utilisateur (optionnel)
        $existing = $em->getRepository(Technicien::class)->findOneBy(['user' => $user]);
        if ($existing) {
            $this->addFlash('warning', 'Ce utilisateur est déjà technicien.');
            return $this->redirectToRoute('user_list');
        }

        $technicien = new Technicien();
        $technicien->setUser($user);

        // Optionnel : ajouter le rôle ROLE_TECHNICIEN à l’utilisateur
        $roles = $user->getRoles();
        if (!in_array('ROLE_TECHNICIEN', $roles)) {
            $roles[] = 'ROLE_TECHNICIEN';
            $user->setRoles($roles);
            $em->persist($user);
        }

        $em->persist($technicien);
        $em->flush();

        $this->addFlash('success', 'Technicien créé à partir de l’utilisateur.');

        return $this->redirectToRoute('user_list');
    }
}








  







    

