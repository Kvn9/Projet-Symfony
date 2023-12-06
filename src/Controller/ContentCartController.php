<?php

namespace App\Controller;

use App\Entity\ContentCart;
use App\Form\ContentCartType;
use App\Repository\ContentCartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/content/cart')]
class ContentCartController extends AbstractController
{
    #[Route('/', name: 'app_content_cart_index', methods: ['GET'])]
    public function index(ContentCartRepository $contentCartRepository): Response
    {
        return $this->render('content_cart/index.html.twig', [
            'content_carts' => $contentCartRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_content_cart_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contentCart = new ContentCart();
        $form = $this->createForm(ContentCartType::class, $contentCart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contentCart);
            $entityManager->flush();

            return $this->redirectToRoute('app_content_cart_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('content_cart/new.html.twig', [
            'content_cart' => $contentCart,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_content_cart_show', methods: ['GET'])]
    public function show(ContentCart $contentCart): Response
    {
        return $this->render('content_cart/show.html.twig', [
            'content_cart' => $contentCart,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_content_cart_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContentCart $contentCart, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContentCartType::class, $contentCart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_content_cart_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('content_cart/edit.html.twig', [
            'content_cart' => $contentCart,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_content_cart_delete', methods: ['POST'])]
    public function delete(Request $request, ContentCart $contentCart, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contentCart->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contentCart);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_content_cart_index', [], Response::HTTP_SEE_OTHER);
    }
}
