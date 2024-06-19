<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ChatGPTService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SearchController extends AbstractController
{
    private $chatGPTService;

    public function __construct(ChatGPTService $chatGPTService)
    {
        $this->chatGPTService = $chatGPTService;
    }
    #[Route(path: '/search', name: 'search')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Ensure user is authenticated
    public function search(Request $request , UserInterface $user): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return new RedirectResponse('/');
        }
        $query = $request->query->get('q', '');

        $result = null; // Initialize result as null

        // Check if there is a query parameter, if so, perform the search
        if (!empty($query)) {
            $result = $this->chatGPTService->search($query);
        }



        // Always pass 'query' to the view, even if it's empty
        return $this->render('search/index.html.twig', [
            'query' => $query,
            'result' => $result
        ]);
    }

}
