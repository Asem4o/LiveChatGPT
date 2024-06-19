<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\User;
use App\Form\NoteType;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class NoteController extends AbstractController
{


    #[Route('/note', name: 'note')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return new RedirectResponse('/');
        }

        $note = new Note();
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setUser($user);

            $entityManager->persist($note);
            $entityManager->flush();

            return $this->redirectToRoute('profile');
        }

        return $this->render('note/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
