<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class NotificationController extends AbstractController
{
    #[Route('/notifications/mark-as-read', name: 'notifications_mark_as_read')]
    #[IsGranted('ROLE_USER')]
    public function markAsRead(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        foreach ($user->getNotifications() as $notification) {
            $notification->setIsRead(true);
            $entityManager->persist($notification);
        }
        $entityManager->flush();

        return $this->redirectToRoute('profile'); // Change 'homepage' to your desired route
    }
}
