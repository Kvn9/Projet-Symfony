<?php

namespace App\Controller;

use App\Entity\ContentCart;
use App\Form\ContentCartType;
use App\Repository\CartRepository;
use App\Repository\ContentCartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Product;

#[Route('/{_locale}/content/cart')]
class ContentCartController extends AbstractController
{

    #[Route('/delete/{id}', name: 'app_content_cart_delete')]
    public function delete(Request $request, ContentCart $contentCart, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $entityManager->remove($contentCart);
        $entityManager->flush();

        $this->addFlash('warning', $translator->trans('cart.produit-delete'));
        return $this->redirectToRoute('app_cart_index');
    }
}
