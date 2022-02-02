<?php

namespace App\Controller;

use App\Entity\Ue;
use App\Entity\Niveau;
use App\Entity\Filiere;
use App\Entity\Matiere;
use App\Entity\Etudiant;
use App\Entity\Inscription;
use App\Entity\NotesEtudiant;
use App\Repository\UeRepository;
use App\Repository\NiveauRepository;
use App\Repository\FiliereRepository;
use App\Repository\MatiereRepository;
use App\Repository\EtudiantRepository;
use App\Repository\InscriptionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UniversgController extends AbstractController
{
    /**
     * @Route("/universg", name="universg")
     */
    public function index(InscriptionRepository $inscriptionRepository, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, MatiereRepository $matiereRepository) : Response
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on consulte les données
        return $this->render('universg/index.html.twig', [
            'controller_name' => 'UniversgController',
            'inscriptions'=>$inscriptionRepository->inscriptionssUser($user),
            'inscriptionsNb'=>$inscriptionRepository->count([
                'user'=>$user
            ]),
            'filieres'=>$filiereRepository->filieresUser($user),
            'filieresNb'=>$filiereRepository->count([
                'user'=>$user
            ]),
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'niveauxNb'=>$niveauRepository->count([
                'user'=>$user
            ]),
            'matieres'=>$matiereRepository->matieresUser($user),
            'matieresNb'=>$matiereRepository->count([
                'user'=>$user
            ]),
        ]);
    }

    /**
     * @Route("essaie/{id}", name="essaie")
     */
    public function essaie(Etudiant $etudiant)
    {
        if ($etudiant->inscrit($etudiant)) {

            return $this->json([
                'inscrit'=>'oui',
                'message'=>'Etudiant inscrit'
    
            ],200);
          
        }
        return $this->json([
            'inscrit'=>'non',
            'message'=>'Etudiant pas inscrit'

        ],200);
    }
}

