<?php

namespace App\Controller;

use App\Entity\Messages;

// Ensure this is the correct entity name
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChatController extends AbstractController
{
    #[Route('/chat', name: 'chat')]
    #[IsGranted('ROLE_USER')]
    public function index(UserRepository $userRepository, Security $security): Response
    {
        $users = $userRepository->findAll();
        $currentUser = $security->getUser();

        return $this->render('chat/index.html.twig', [
            'users' => $users,
            'currentUserId' => $currentUser->getId(),
            'currentUsername' => $currentUser->getUsername(),
        ]);
    }

    #[Route('/chat/{id}', name: 'chat_with_user')]
    #[IsGranted('ROLE_USER')]
    public function chat($id, UserRepository $userRepository, Security $security): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $currentUser = $security->getUser();
        $currentUserId = $currentUser->getId();
        $currentUsername = $currentUser->getUsername();

        return $this->render('chat/chat.html.twig', [
            'targetUserId' => $user->getId(),
            'targetUsername' => $user->getUsername(),
            'targetUserProfilePicture' => $user->getProfilePicturePath(),
            'currentUserId' => $currentUserId,
            'currentUsername' => $currentUsername,
            'currentUserProfilePicture' => $currentUser->getProfilePicturePath(),
        ]);
    }

    #[Route('/chat/history/{room}', name: 'chat_history')]
    public function history(string $room, EntityManagerInterface $em): JsonResponse
    {
        $messages = $em->getRepository(Messages::class)->findBy(['room' => $room], ['timestamp' => 'ASC']);

        if (!$messages) {
            return new JsonResponse(['error' => 'No messages found'], Response::HTTP_NOT_FOUND);
        }

        $response = array_map(function (Messages $message) {
            return [
                'user' => $message->getUser()->getUsername(),
                'message' => $message->getMessage(),
                'timestamp' => $message->getTimestamp()->format('c'),
            ];
        }, $messages);

        return new JsonResponse($response);
    }
}
