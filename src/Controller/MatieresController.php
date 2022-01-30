<?php

namespace App\Controller;

use App\Entity\Matiere;
use App\Repository\MatiereRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MatieresController extends AbstractController
{
    /**
     * @Route("/matieres", name="matieres")
     */
    public function index(): Response
    {
        return $this->render('matieres/index.html.twig', [
            'controller_name' => 'MatieresController',
        ]);
    }

     /**
     * @Route("matieres", name="matieres")
     */
    public function matieres (MatiereRepository $matiereRepository, Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on insert les données
        if (!empty($request->request->get('nom_mat'))) {
            $matiere=new Matiere();
            $matiere->setUser($user);
            $matiere->setNom($request->request->get('nom_mat'));
            $matiere->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($matiere);
            $manager->flush();

            return $this->redirectToRoute('matieres');
        } 

        return $this->render('universg/matieres.html.twig', [
            'controller_name' => 'UniversgController',
            'matieres'=>$matiereRepository->matieresUser($user)
        ]);
    }

    /**
     * @Route("matiere_edit/{id}", name="matiere_edit")
     */
    public function matiere_edit (Matiere $matiere, Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on modifie les données
        if (!empty($request->request->get('nom_mat'))) {
            $matiere->setNom($request->request->get('nom_mat'));
            $matiere->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->flush();

            return $this->redirectToRoute('matieres');
        } 

        $repos=$this->getDoctrine()->getRepository(Matiere::class);
        $matieres = $repos->findAll();
        return $this->render('universg/matiere_edit.html.twig', [
            'controller_name' => 'UniversgController',
            'matieres'=>$matieres,
            'matiere'=>$matiere
        ]);
    }

    /**
     * Suppression des matieres
     * @Route("matiere/suppression/{id}", name="suppression_matiere")
     */
    public function suppression_matiere (Matiere $matiere, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        //sinon on supprime les données
        $manager = $end->getManager();
        $manager->remove($matiere);
        $manager->flush();

        return $this->redirectToRoute('matieres');
        
    }
    
}
