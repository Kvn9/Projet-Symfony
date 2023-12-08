<?php

namespace App\Controller;

use App\Entity\ContentCart;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Cart;

#[Route('/{_locale}')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/product/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('picture')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', $translator->trans('product.impossibleajoutimage'));
                }
                $product->setPicture($newFilename);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('product.formationadd'));
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'add' => $form,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        if ($product == null) {
            $this->addFlash('danger',  $translator->trans('product.model'));
            return $this->redirectToRoute('app_model');
        }

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('picture')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', $translator->trans('product.impossibleajoutimage'));
                }
                $product->setPicture($newFilename);
            }
            $em->persist($product);
            $em->flush();
            $this->addFlash('success', $translator->trans('product.miseajour'));

            return $this->redirectToRoute('app_product_index');
        }
        return $this->render('product/edit.html.twig', [
            'edit' => $form->createView(),
        ]);
    }

    #[Route('/product/delete/{id}', name: 'app_product_delete')]
    public function delete(Product $product, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        if ($product == null) {
            $this->addFlash('danger', $translator->trans('product.introuvable'));
            return $this->redirectToRoute('app_product_index');
        }

        $em->remove($product);
        $em->flush();


        $this->addFlash('danger', $translator->trans('product.delete'));
        return $this->redirectToRoute('app_product_index');
    }
    #[Route('/product/add/{id}', name: 'app_add_content_cart', methods: ['GET', 'POST'])]
    public function toCart(Request $request, EntityManagerInterface $em, CartRepository $cartRepository, Product $product, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();

        if ($request->request->has('add-cart')) {
            if (is_null($user)) {
                return $this->redirectToRoute('app_login');
            }

            $falseStatesList = $cartRepository->findByStateFalse($user->getId());
            if (empty($falseStatesList)) {
                $cart = new Cart();
                $cart->setUser($user)
                    ->setState(false);
                $em->persist($cart);
                $em->flush();
            } else {
                $cart = $falseStatesList[0];
            }

            $contentCart = new ContentCart();
            $contentCart->setCart($cart)
                ->setProduct($product)
                ->setQuantity($request->request->get('productQuantity'))
                ->setCreatedAt(new \DateTime());

            $em->persist($contentCart);
            $em->flush();
            $this->addFlash('success', $translator->trans('cart.produit'));
        }
        return $this->redirectToRoute('app_product_index', ['id' => $product->getId()]);
    }
}
