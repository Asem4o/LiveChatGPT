<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function home()
    {
// Render your homepage
        return $this->render('default/home.html.twig');
    }

    public function index()
    {
// Redirect to the homepage
        return $this->redirectToRoute('app_login');
    }
}
