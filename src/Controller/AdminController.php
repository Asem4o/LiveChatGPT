<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Note;
use App\Form\NoteType;
use App\Repository\UserRepository;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin/profile', name: 'admin_profile')]
    public function profile(UserRepository $userRepository, Security $security, AuthenticationUtils $authenticationUtils): Response
    {
        $adminUser = $security->getUser();
        $lastUsername = $authenticationUtils->getLastUsername();
        $users = $userRepository->findAll();

        return $this->render('admin/profile.html.twig', [
            'user' => $adminUser,
            'email' => $lastUsername,
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/{id}/notes', name: 'admin_user_notes', methods: ['GET'])]
    public function userNotes(User $user, NoteRepository $noteRepository): Response
    {
        $notes = $noteRepository->findBy(['user' => $user]);

        return $this->render('admin/note/user_notes.html.twig', [
            'notes' => $notes,
            'user' => $user,
        ]);
    }

    #[Route('/admin/note/new/{userId}', name: 'admin_note_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $userId, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($userId);
        $note = new Note();
        $note->setUser($user);
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($note);
            $entityManager->flush();

            $notification = new Notification();
            $notification->setUser($user);
            $notification->setMessage('The note "' . $note->getContent() . '" has been created in your profile.');
            $entityManager->persist($notification);
            $entityManager->flush();

            return $this->redirectToRoute('admin_user_notes', ['id' => $userId]);
        }

        return $this->render('admin/note/new.html.twig', [
            'note' => $note,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/note/{id}/edit', name: 'admin_note_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Note $note, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $notification = new Notification();
            $notification->setUser($note->getUser());
            $notification->setMessage('The note "' . $note->getContent() . '" has been edited.');
            $entityManager->persist($notification);
            $entityManager->flush();

            return $this->redirectToRoute('admin_user_notes', ['id' => $note->getUser()->getId()]);
        }

        return $this->render('admin/note/edit.html.twig', [
            'note' => $note,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/note/{id}', name: 'admin_note_delete', methods: ['POST'])]
    public function delete(Request $request, Note $note, EntityManagerInterface $entityManager): Response
    {
        $userId = $note->getUser()->getId();
        if ($this->isCsrfTokenValid('delete' . $note->getId(), $request->request->get('_token'))) {
            $entityManager->remove($note);
            $entityManager->flush();
            $notification = new Notification();
            $notification->setUser($note->getUser());
            $notification->setMessage('The note "' . $note->getContent() . '" has been deleted.');
            $entityManager->persist($notification);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_user_notes', ['id' => $userId]);
    }
}
