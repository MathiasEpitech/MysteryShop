<?php

namespace App\Controller;

use App\Entity\Produitmf;
use App\Form\ProduitFormType;
use App\Repository\ProduitmfRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/produits', name: 'app_admin_produits')]
    public function adminProduits(ProduitmfRepository $produitmfRepo): Response
    {
        $produits =$produitmfRepo->findAll();


        return $this->render('admin/produits.html.twig',[
            'produits' => $produits
        ]);
    }

    #[Route('/admin/produits/ajouter', name: 'app_admin_produit_ajouter')]
    public function ajouterProduit(Request $request,EntityManagerInterface $manager, SluggerInterface $slugger): Response
    {

        $produits = new Produitmf;

        $form = $this->createForm(ProduitFormType::class, $produits);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {

            /** @var UploadedFile $imagefile */
            $imageFile = $form->get('image')->getData();

            if($imageFile)
            {
                $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFileName = $slugger->slug($originalFileName);

                $newFileName = $safeFileName . '-' . uniqid() . '_' . $imageFile->guessExtension();

                try
                {
                    $imageFile->move($this->getParameter('image_directory'), $newFileName);
                }
                catch(FileException $e)
                {
                    die("Erreur: " . $e->getMessage());
                }

                $produits->setImage($newFileName);
            }

            $manager->persist($produits);

            $manager->flush();

            $this->addFlash('success', "Le produit " .$produits->getId() . " a bien ete ajouter");

            return $this->redirectToRoute("app_admin_produits");
        }

        return $this->render('admin/produit_ajouter.html.twig',[
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/produits/editer/{id}', name: 'app_admin_produit_editer')]
    public function editerProduit(Produitmf $produitmfRepo = null, Request $request, EntityManagerInterface $manager): Response
    {
        if(!$produitmfRepo)
        {
            $produitmfRepo = new Produitmf;
        }

        $id = $produitmfRepo->getId();

        if (!is_numeric($id) || is_null($id))
        {
            $this->addFlash('warning', "Ce produit n'existe pas ");

            return $this->redirectToRoute("app_admin_produits");
        }

        $form = $this->createForm(ProduitFormType::class, $produitmfRepo);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->flush();

            $this->addFlash("info", "Le produit n° $id a ete modifier");

            return $this->redirectToRoute("app_admin_produits");
        }

        return $this->render('admin/produit_editer.html.twig',[
            'form' => $form->createView(),
            'id' => $id
        ]);

    }

    #[Route('/admin/produits/supprimer/{id}', name: 'app_admin_produit_supp')]
    public function suppProduit(Produitmf $produitmfRepo, EntityManagerInterface $manager): Response
    {
        $manager->remove($produitmfRepo);

        $manager->flush();

        $this->addFlash("danger", "Le produit". $produitmfRepo->getNom() . " a bien ete supprimé");
        
        return $this->redirectToRoute("app_admin_produits");

    }
}
