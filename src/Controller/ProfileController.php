<?php

declare(strict_types=1);

namespace App\Controller;


use App\Entity\Note;
use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// Import your entity

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function profile(NotificationRepository $notificationRepository, UserInterface $user, Security $security, EntityManagerInterface $entityManager, AuthenticationUtils $authenticationUtils): Response
    {


        $userNotes = $security->getUser();
        $noteRepository = $entityManager->getRepository(Note::class);
        $userNotification = $this->getUser();
        $notifications = $notificationRepository->findBy(['user' => $userNotification, 'isRead' => false]);
        $notes = $noteRepository->findBy(['user' => $userNotes->getId()]);
        $created = null;
        if (!empty($notes)) {
            $created = $notes[0]->getCreated(); // Assuming 'getCreated()' method exists in Note entity
            $created = $created->format('Y-m-d H:i:s'); // Format the DateTime object into a string
        }

        if ($user && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('admin_profile');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'notes' => $notes,
            'created' => $created, // Pass the 'created' date to the template
            'notifications' => $notifications,
        ]);
    }

    #[Route('/profile/edit', name: 'profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process profile picture
            $file = $form->get('profilePicture')->getData();
            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('profile_pictures_directory'), $filename);
                $user->setProfilePicturePath($filename);
                $entityManager->flush();  // Persist changes for the profile picture
            }

            // Process password change if fields are filled
            if (!empty($form->get('oldPassword')->getData()) && !empty($form->get('newPassword')->getData())) {
                $oldPassword = $form->get('oldPassword')->getData();
                $newPassword = $form->get('newPassword')->getData();
                $confirmPassword = $form->get('confirmPassword')->getData();

                if ($passwordHasher->isPasswordValid($user, $oldPassword)) {
                    if ($newPassword === $confirmPassword) {
                        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                        $entityManager->flush();  // Persist password change
                    } else {
                        $form->get('confirmPassword')->addError(new FormError("New password and confirm password do not match"));
                    }
                } else {
                    $form->get('oldPassword')->addError(new FormError("Old password is incorrect"));
                }
            }

            if ($form->isSubmitted()) {
                return $this->redirectToRoute('profile');  // Redirect after successful submission
            }
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }


}
