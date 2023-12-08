<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\CartRepository;


class SuperAdminController extends AbstractController
{
    #[Route('/{_locale}/admin', name: 'app_super_admin')]
    public function index(
        EntityManagerInterface $em,
        Request $request,
        CartRepository $cartRepository,
        UserRepository $userRepository
    ): Response
    {
        $user = $this->getUser();

        if($user) {
            if ($this->isGranted('ROLE_SUPER_ADMIN')) {
                $today = new \DateTime();

                $allCarts = $cartRepository->findBy(['user' => $user, 'state' => false]);
                $users = $userRepository->findUsersRegisteredToday();

            } else {
                return $this->redirectToRoute('app_product_index');
            }
        } else {
            return $this->redirectToRoute('app_login');

        }
        return $this->render('super_admin/index.html.twig', [
            'carts' => $allCarts,
            'users' => $users
        ]);
    }
}
