<?php

namespace App\Controller;

use DateTime;
use App\Entity\Avis;
use App\Entity\Produitmf;
use App\Form\AvisFormType;
use App\Repository\AvisRepository;
use App\Repository\CategorieRepository;
use App\Repository\ProduitmfRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProduitController extends AbstractController
{
    /**
     * @Route("/produit", name="app_produit")
     */
    public function index(ProduitmfRepository $produitmfRepo): Response
    {
        $produits = $produitmfRepo->findAll();

        return $this->render('produit/index.html.twig',[
            'produits' =>$produits
        ]);
    }


    /**
     * @Route("/produit/{id}", name="app_detail_produit", requirements={"id"="\d+"})
     */
    public function detail($id,Produitmf $produitmf = null, ProduitmfRepository $produitmfRepo,Request $request, EntityManagerInterface $manager): Response
    {
        if(!$produitmf)
        {
            $produitmf = new Produitmf;
        }

        $id = $produitmf->getId();

        if (!is_numeric($id) || is_null($id))
        {
            $this->addFlash('warning', "Ce produit n'existe pas ");

            return $this->redirectToRoute("app_produit");
        }
        $avis= new Avis;

        $form = $this->createForm(AvisFormType::class, $avis);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $avis->setCreateAt(new \DateTime())
                 ->setProduit($produitmfRepo->find($id));

            $manager->persist($avis);

            $manager->flush();

            $this->addFlash("success","Votre avis a bien ete poster");

            return $this->redirectToRoute('app_detail_produit', [
                'id' => $id
            ]);
        }

        return $this->render('produit/detail.html.twig',[
            'produit' => $produitmfRepo->find($id),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/categories", name="app_categories")
     */
    public function categoriesAll(CategorieRepository $catRepo): Response
    {
        $categories = $catRepo->findAll();

        return $this->render('produit/categories.html.twig', [
            'categories' => $categories

        ]);
    }

    /**
     * @Route("/categorie/{id}", name="app_categorie_produit")
     */
    public function categorieProduit($id,CategorieRepository $catRepo): Response
    {
        $categorie = $catRepo->find($id);

        if(!$categorie)
        {
            $this->addFlash("warning","Cette categorie n'existe pas");

            return $this->redirectToRoute('app_categories');
        }

        return $this->render('produit/categorie_produit.html.twig', [
            'categorie' => $categorie

        ]);
    }
}
