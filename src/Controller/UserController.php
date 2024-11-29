<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{

    #[Route('/profile', name: 'user_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Assure que l'utilisateur est connecté
    public function profile(): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
