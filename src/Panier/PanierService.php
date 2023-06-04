<?php

namespace App\Panier;

use App\Panier\ProduitDansPanier;
use App\Repository\ProduitmfRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PanierService{

    protected $session;

    protected $produitmfRepo;

    public function __construct(SessionInterface $session,ProduitmfRepository $produit)
    {
        $this->session = $session;
        $this->produitmfRepo = $produit;
    }


    public function ajouter($id)
    {
        $panier = $this->session->get('panier', []);

        if(array_key_exists($id, $panier))
        {
            $panier[$id]++;
        }
        else
        {
            $panier[$id] = 1;
        }

        $this->session->set('panier', $panier);
    }

    public function getDetail()
    {
        $panier = [];

        foreach($this->session->get('panier', [] ) as $id => $qte)
        {

            $produit = $this->produitmfRepo->find($id);

            if(!$produit)
            {
                continue;

            }

            $panier[]= new ProduitDansPanier($produit,$qte);

        }

        return $panier;

    }

    public function getTotal()
    {
        $total = 0;

        foreach($this->session->get('panier', [] ) as $id => $qte)
        {

            $produit = $this->produitmfRepo->find($id);

            if(!$produit)
            {
                continue;
            }

            $total += ($produit->getPrix() * $qte);
        }

        return $total;

    }

    public function supprimer($id)
    {
        $panier = $this->session->get('panier', [] );

        unset($panier[$id]);

        $this->session->set('panier', $panier);
    }

    public function decrementer($id)
    {
        $panier = $this->session->get('panier', []);

        if(!array_key_exists($id, $panier))
        {
            return;
        }
        if($panier[$id] === 1)
        {
            $this->supprimer($id);
            return;
        }
        else
        {
            $panier[$id]--;
        }

        $this->session->set('panier', $panier);
    }

    public function vider()
    {
        return $this->session->set('panier',[]);
    }

}
