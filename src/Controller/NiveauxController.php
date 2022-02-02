<?php

namespace App\Controller;

use App\Entity\Niveau;
use App\Repository\NiveauRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("niveaux_", name="niveaux_")
 */
class NiveauxController extends AbstractController
{
   
    /**
     * Insertion et affichage des niveaux
     * @Route("ajoutEt_liste", name="ajoutEt_liste")
     */
    public function ajoutEt_liste (NiveauRepository $niveauRepository,Request $request, ManagerRegistry $end)
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

            return $this->redirectToRoute('niveaux_ajoutEt_liste');
        } 
        
        return $this->render('niveaux/niveaux.html.twig', [
            'controller_name' => 'NiveauxController',
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'niveauxNb'=>$niveauRepository->count([
                'user'=>$user
            ]),
        ]);
    }

    /**
     * Suppression des niveaux
     * @Route("suppression/{id}", name="suppression")
     */
    public function suppression (Niveau $niveau, ManagerRegistry $end)
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

        return $this->redirectToRoute('niveaux_ajoutEt_liste');
        
        return $this->render('niveaux/niveaux.html.twig', [
            'controller_name' => 'NiveauxController',
        ]);
    }

    /**
     * @Route("infos/{id}", name="infos")
     */
    public function infos(Niveau $niveau)
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
        return $this->render('niveaux/consultation_niveau.html.twig', [
            'controller_name' => 'UniversgController',
            'niveau'=>$niveau
        ]);
    }
    
}
