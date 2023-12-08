<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CartRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // Rend la page d'index des utilisateurs avec la liste de tous les utilisateurs
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/me', name: 'app_user')]
    public function user(
        EntityManagerInterface $em,
        Request $request,
        CartRepository $cartRepository
    ): Response {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();
        $allCarts = [];

        // Vérifie si l'utilisateur est authentifié
        if ($user) {
            // Crée un formulaire de modification pour l'utilisateur actuel
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            // Récupère les paniers de l'utilisateur
            $allCarts = $cartRepository->findBy(['user' => $user, 'state' => true]);

            // Vérifie si le formulaire a été soumis et est valide
            if ($form->isSubmitted() && $form->isValid()) {
                // Enregistre les modifications de l'utilisateur en base de données
                $em->persist($user);
                $em->flush();

                // Ajoute un message flash pour informer de la modification réussie
                $this->addFlash('success', 'Utilisateur modifié');
            }
        } else {
            // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
            return $this->redirectToRoute('app_login');
        }

        // Rend la page de modification de l'utilisateur avec les informations nécessaires
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'edit' => $form->createView(),
            'carts' => $allCarts
        ]);
    }


    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Crée un nouvel utilisateur et un formulaire d'inscription
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistre le nouvel utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirige vers la page d'index des utilisateurs après l'enregistrement
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        // Rend la page d'inscription avec le formulaire
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        // Rend la page de détails d'un utilisateur
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_user_delete')]
    public function delete(
        Request $request,
        User $user = null,
        EntityManagerInterface $em,
    ): Response {
        // Vérifie si l'utilisateur existe
        if ($user == null) {
            // Ajoute un message flash si l'utilisateur est introuvable
            $this->addFlash('danger', 'Utilisateur introuvable');
            return $this->redirectToRoute('app_user_index');
        }

        // Vérifie si le token CSRF est valide
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            // Ajoute un message flash si le token CSRF est invalide
            $this->addFlash('danger', 'Token CSRF invalide');

            // Supprime l'utilisateur de la base de données
            $em->remove($user);
            $em->flush();

            // Ajoute un message flash pour informer de la suppression réussie
            $this->addFlash('success', 'Utilisateur supprimé');
        }

        // Redirige vers la page d'index des utilisateurs
        return $this->redirectToRoute('app_user_index');
    }
}
