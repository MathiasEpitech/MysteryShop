<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_admin_user')]
    public function index(UserRepository $userRepo): Response
    {
        $users = $userRepo->findAll();

        return $this->render('admin_user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/editer/{id}', name: 'app_admin_user_editer')]
    public function editerUser(User $user = null, Request $request, EntityManagerInterface $manager): Response
    {
        if(!$user)
        {
            $user = new User;
        }

        $id = $user->getId();

        if (!is_numeric($id) || is_null($id))
        {
            $this->addFlash('warning', "Cette utilisateur n'existe pas ");

            return $this->redirectToRoute("app_admin_user");
        }

        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->flush();

            $this->addFlash("info", "L'utilisateur ". $user->getNom() . " a bien été modifié");

            return $this->redirectToRoute('app_admin_user');
        }

        return $this->render('admin_user/user_editer.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/user/supprimer/{id}', name: 'app_admin_user_supp')]
    public function suppProduit(User $user = null, EntityManagerInterface $manager): Response
    {
        if(!$user)
        {
            $user = new User;
        }

        $id = $user->getId();

        if (!is_numeric($id) || is_null($id))
        {
            $this->addFlash('warning', "Cette utilisateur n'existe pas ");

            return $this->redirectToRoute("app_admin_user");
        }

        $manager->remove($user);

        $manager->flush();

        $this->addFlash("danger", "L'utilisateur ". $user->getNom() . " a bien ete supprimé");
        
        return $this->redirectToRoute("app_admin_user");

    }
}
