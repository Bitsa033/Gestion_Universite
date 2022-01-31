<?php

namespace App\Controller;

use App\Entity\Niveau;
use App\Repository\NiveauRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NiveauxController extends AbstractController
{
    /**
     * @Route("/niveaux", name="niveaux")
     */
    public function index(): Response
    {
        return $this->render('niveaux/index.html.twig', [
            'controller_name' => 'NiveauxController',
        ]);
    }

    /**
     * Insertion et affichage des niveaux
     * @Route("niveaux", name="niveaux")
     */
    public function niveau (NiveauRepository $niveauRepository,Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        //sinon on insert les données
        if (!empty($request->request->get('nom_niv'))) {
            $niveau=new Niveau();
            $niveau->setUser($user);
            $niveau->setNom(strtoupper($request->request->get('nom_niv')));
            $niveau->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($niveau);
            $manager->flush();

            return $this->redirectToRoute('niveaux');
        } 
        
        return $this->render('universg/niveaux.html.twig', [
            'controller_name' => 'NiveauxController',
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'niveauxNb'=>$niveauRepository->count([
                'user'=>$user
            ]),
        ]);
    }

    /**
     * Suppression des niveaux
     * @Route("niveau/suppression/{id}", name="suppression_niveau")
     */
    public function suppression_niveau (Niveau $niveau, ManagerRegistry $end)
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
        $manager->remove($niveau);
        $manager->flush();

        return $this->redirectToRoute('niveau');
        
        return $this->render('universg/niveaux.html.twig', [
            'controller_name' => 'NiveauxController',
        ]);
    }

    /**
     * @Route("consultation/niveau/{id}", name="consultation_niveau")
     */
    public function consultation_niveau(Niveau $niveau)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on consulte les données
        //affichage d'un niveau particulier
        return $this->render('universg/consultation_niveau.html.twig', [
            'controller_name' => 'UniversgController',
            'niveau'=>$niveau
        ]);
    }
    
}
