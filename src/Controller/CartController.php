<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Form\CartType;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ContentCartRepository;


#[Route('/{_locale}/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index', methods: ['GET'])]
    public function index(CartRepository $cartRepository, EntityManagerInterface $em, ContentCartRepository $contentCartRepository): Response
    {
        $user = $this->getUser();
        $contentCarts = [];
        if($user) {
            $carts = $cartRepository->findByStateFalse($user->getId());
            if(empty($carts)) {
                $cart = new Cart();
                $cart->setUser($user)
                    ->setState(false);
                $em->persist($cart);
                $em->flush();
            } else {
                $cart = $carts[0];
            }
            $contentCarts = $contentCartRepository->findByCartId($cart->getId());

        } else {
            return $this->redirectToRoute('app_login');
        }


        return $this->render('cart/index.html.twig', [
            'contentCarts' => $contentCarts,
            'cartId' => $cart->getId()
        ]);
    }


    #[Route('/pay/{id}', name: 'app_cart_pay', methods: ['GET', 'POST'])]
    public function pay(Request $request, Cart $cart, EntityManagerInterface $em, ContentCartRepository $contentCartRepository): Response
    {
        $can = true;
        $contentCarts = $contentCartRepository->findByCartId($cart->getId());

        foreach ($contentCarts as $content) {
            $quantity = $content->getQuantity();
            $product = $content->getproduct();
            $product->setQuantity($product->getQuantity() - $quantity);
            $em->persist($product);
            $em->flush();
            if($product->getQuantity() <= 0) {
                $em->remove($content);
                $can = false;
            }

        }
        if($can) {
            $cart->setState(true)
                ->setBuyAt(new \DateTime());

            $em->persist($cart);
            $em->flush();
            return $this->redirectToRoute('app_product_index');
        }

        $this->addFlash('danger', 'Des formations sont en rupture de stock, elles ont été retiré du panier.');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/all', name: 'app_cart_all', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cart $cart, EntityManagerInterface $entityManager): Response
    {
//TODO bloqué les user qui ne sont pas super admin
        return $this->render('cart/edit.html.twig', [

        ]);
    }

}
