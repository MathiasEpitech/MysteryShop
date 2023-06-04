<?php

namespace App\Controller;

use DateTime;
use Stripe\Stripe;
use App\Entity\Commande;
use Stripe\PaymentIntent;
use App\Form\PanierFormType;
use Stripe\Checkout\Session;
use App\Panier\PanierService;
use App\Entity\CommandeDetail;
use App\Repository\ProduitmfRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PanierController extends AbstractController
{
    #[Route('/panier/ajouter/{id}', name: 'app_panier_ajouter')]
    public function ajouter($id, ProduitmfRepository $produitmfRepo, PanierService $panServ): Response
    {
        $produit = $produitmfRepo->find($id);

        if (!$produit) {
            $this->addFlash('warning', "Ce produit n'est plus disponible");

            return $this->redirectToRoute("app_produit");
        }

        $panServ->ajouter($id);

        $this->addFlash('success', "Le produit a bien ete ajouter");

        return $this->redirectToRoute("app_panier");
    }

    #[Route('/panier', name: 'app_panier')]
    public function voirPanier(PanierService $panServ, Request $request): Response
    {
        $form = $this->createForm(PanierFormType::class);

        $panier = $panServ->getDetail();

        $total = $panServ->getTotal();

        return $this->render("panier/index.html.twig", [
            'panier' => $panier,
            'total' => $total,
            'form' => $form->createView()

        ]);
    }

    #[Route('/panier/supprimer/{id}', name: 'app_panier_supp')]
    public function supprimer($id, produitmfRepository $produitmfRepo, PanierService $panServ): Response
    {
        $produit = $produitmfRepo->find($id);

        if (!$produit) {
            $this->addFlash('warning', "Ce produit n'existe pas");

            return $this->redirectToRoute("app_panier");
        }

        $panServ->supprimer($id);

        $this->addFlash('warning', "Ce produit a ete supprimer du panier");

        return $this->redirectToRoute("app_panier");
    }

    #[Route('/panier/decrementer/{id}', name: 'app_panier_decrementer')]
    public function decrementer($id, PanierService $panServ): Response
    {
        $panServ->decrementer($id);

        return $this->redirectToRoute("app_panier");
    }

    #[Route('/panier/confirmation', name: 'app_panier_confirmation')]
    public function confirm(Request $request, PanierService $panServ, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(PanierFormType::class);

        $form->handleRequest($request);

        $panierDetail = $panServ->getDetail();

        if (count($panierDetail) === 0) {
            $this->addFlash('warning', "Votre panier est vide");
            return $this->redirectToRoute('app_panier');
        }

        

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if (!$user) {
                $this->addFlash('warning', 'Vous devez vous connecter pour valider la commande');
                return $this->redirectToRoute('app_panier');
            }

      
            /**@var Commande */
            $commande = $form->getData();

            $commande->setUser($user)
         ->setDateCommande(new DateTime())
         ->setTotal($panServ->getTotal());

                 
            $manager->persist($commande);

            foreach ($panServ->getDetail() as $unite) {
                $commandeDetail = new CommandeDetail;

                $commandeDetail->setCommande($commande)
                   ->setProduit($unite->produit)
                   ->setNomProduit($unite->produit->getNom())
                   ->setQuantite($unite->qte)
                   ->setTotal($unite->getTotal())
                   ->setPrixProduit($unite->produit->getPrix());

                $manager->persist($commandeDetail);
            }
 
       
            Stripe::setApiKey('sk_test_51LNyGsBTUkmZ4dtBy6p5d1TfWPF59pDSzRbeCiVmiFXk9cRQmOIlMgGsVu5apNrNUNhiO6ZKLeSMTMMvNVp6N4Mh00weEQfUom');

                    $checkout_session = Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'EUR',
                            'product_data' => [
                                'name' => $commandeDetail->getNomProduit()
                            ],
                            'unit_amount' => ($commandeDetail->getPrixProduit() * 100)
                        ],
                        'quantity' => $commandeDetail->getQuantite(),
                    ]],
                    'mode' => 'payment',
                    'success_url' => $this->generateUrl('app_panier_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    'cancel_url' => $this->generateUrl('app_panier_defaut', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ]);

            $panServ->vider();

            $commande->setStatus('VALIDE');
            $manager->persist($commande);
            $manager->flush();

            return $this->redirect("$checkout_session->url");
        } else {
            $this->addFlash('warning', "Vous devez remplir le formulaire pour valider votre commande");
            return $this->redirectToroute('app_panier');
        }
    }

    #[Route('/panier/success', name:'app_panier_success')]
    public function success(): Response
    {
        return $this->render('panier/confirmation.html.twig');
    }

    #[Route('/panier/detail_commande', name: 'app_panier_detail_commande')]
    public function detailCommande(): Response
    {
        $user = $this->getUser();

        $commandes = $user->getCommandes();


        if (!$user) {
            $this->redirectToRoute('app_login');
        }

        return $this->render('panier/detail_commande.html.twig', [
            'commandes' => $commandes
        ]);
    }

    #[Route('/panier/annulation', name:'app_panier_defaut')]
    public function annuler(): Response
    {
        return $this->render('panier/default.html.twig');
    }
}
