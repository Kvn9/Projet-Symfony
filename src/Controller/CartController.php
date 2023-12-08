<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ContentCartRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index', methods: ['GET'])]
    public function index(CartRepository $cartRepository, EntityManagerInterface $em, ContentCartRepository $contentCartRepository, TranslatorInterface $translator): Response
    {
        // Récupère l'utilisateur actuel
        $user = $this->getUser();
        $contentCarts = [];

        if ($user) {
            // Récupère les paniers non payés de l'utilisateur
            $carts = $cartRepository->findByStateFalse($user->getId());

            if (empty($carts)) {
                // Crée un nouveau panier s'il n'y en a pas
                $cart = new Cart();
                $cart->setUser($user)
                    ->setState(false);
                $em->persist($cart);
                $em->flush();
            } else {
                $cart = $carts[0];
            }

            // Récupère les contenus du panier
            $contentCarts = $contentCartRepository->findByCartId($cart->getId());
        } else {
            // Ajout d'un message Flash pour informer l'utilisateur de se connecter
            $this->addFlash('warning', $translator->trans('cart.connecter'));
            return $this->redirectToRoute('app_login');
        }

        // Affiche la page du panier avec les contenus et l'ID du panier
        return $this->render('cart/index.html.twig', [
            'contentCarts' => $contentCarts,
            'cartId' => $cart->getId(),
        ]);
    }

    #[Route('/pay/{id}', name: 'app_cart_pay', methods: ['GET', 'POST'])]
    public function pay(Request $request, Cart $cart, EntityManagerInterface $em, ContentCartRepository $contentCartRepository, TranslatorInterface $translator): Response
    {
        // Variable pour vérifier si le paiement peut être effectué
        $can = true;
        $contentCarts = $contentCartRepository->findByCartId($cart->getId());

        // Parcours les contenus du panier
        foreach ($contentCarts as $content) {
            $quantity = $content->getQuantity();
            $product = $content->getproduct();
            $product->setStock($product->getStock() - $quantity);
            $em->persist($product);
            $em->flush();
            if($product->getStock() <= 0) {

                $em->remove($content);
                $can = false;
            }
        }

        if ($can) {
            // Marque le panier comme payé et enregistre la date du paiement
            $cart->setState(true)
                ->setBuyAt(new \DateTime());

            $em->persist($cart);
            $em->flush();

            // Ajout d'un message Flash pour informer de la réussite du paiement
            $this->addFlash('success', $translator->trans('cart.success'));
            return $this->redirectToRoute('app_product_index');
        }

        // Ajout d'un message Flash pour informer de la rupture de stock
        $this->addFlash('danger', $translator->trans('cart.danger'));

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/all', name: 'app_cart_all', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        // TODO bloquer les utilisateurs qui ne sont pas super admins
        return $this->render('cart/edit.html.twig', []);
    }
}
