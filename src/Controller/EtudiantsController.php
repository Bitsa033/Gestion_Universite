<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Entity\Inscription;
use App\Repository\NiveauRepository;
use App\Repository\FiliereRepository;
use App\Repository\EtudiantRepository;
use App\Repository\InscriptionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EtudiantsController extends AbstractController
{
    /**
     * @Route("/etudiants", name="etudiants")
     */
    public function index(): Response
    {
        return $this->render('etudiants/index.html.twig', [
            'controller_name' => 'EtudiantsController',
        ]);
    }

    /**
     * Creation des etudiants
     * @Route("create", name="creation_etudiants")
     */
    public function creation_etudiants (Request $request,ManagerRegistry $end_e)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on insert les données

       if (!empty($request->request->get('nom_et')) && 
                !empty($request->request->get('prenom_et')) &&
                !empty($request->request->get('sexe_et'))) {
                //on enregistre l'etudiant
                $etudiant=new Etudiant();
                $etudiant->setUser($user);
                $etudiant->setNom(strtoupper($request->request->get("nom_et")));
                $etudiant->setprenom(ucfirst($request->request->get("prenom_et")));
                $etudiant->setSexe(strtoupper($request->request->get("sexe_et")));
                $etudiant->setCreatedAt(new \DateTime());
                $manager = $end_e->getManager();
                $manager->persist($etudiant);
                $manager->flush();

            return $this->redirectToRoute('etudiants');
       }
        return $this->render('universg/creation_etudiants.html.twig', [
            'controller_name' => 'EtudiantsController',
        ]);
    }

    /**
     * On affiche les etudiants enregistrés en fonction de
     * l'utilisateur connecté
     * @Route("etudiants", name="etudiants")
     */
    public function etudiants (EtudiantRepository $etudiantRepository)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        
        return $this->render('universg/etudiants.html.twig', [
            'controller_name' => 'EtudiantsController',
            'etudiants'=>$etudiantRepository->etudiantsUser($user),
            'nbEtudiants'=>$etudiantRepository->count([])
        ]);
    }

    /**
     * On supprime un etudiant par son id
     * @Route("etudiant/suppression/{id}", name="suppression_etudiant")
     */
    public function suppression_etudiant (Etudiant $etudiant, ManagerRegistry $end)
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
        $manager->remove($etudiant);
        $manager->flush();

        return $this->redirectToRoute('etudiants');
        
    }

    /**
     * On inscrit l'etudiant
     * @Route("inscription_etudiants", name="inscription_etudiants")
     */
    public function inscription_etudiants(EtudiantRepository $etudiantRepository, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,Request $request,ManagerRegistry $end_e )
        {

        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on insert les données
        
        if (
            !empty($request->request->get('niveau')) && 
            !empty($request->request->get('filiere')) &&
            !empty($request->request->get('etudiant'))
        ) {
    
            $niveau = $niveauRepository->find($request->request->get("niveau"));
            $filiere = $filiereRepository->find($request->request->get("filiere"));
            $etudiant = $etudiantRepository->find($request->request->get("etudiant"));
           
            $inscription=new Inscription();
            $inscription->setUser($user);
            $inscription->setEtudiant($etudiant);
            $inscription->setFiliere($filiere);
            $inscription->setNiveau($niveau);
            $inscription->setCreatedAt(new \DateTime());
            //$manager=$mg->getManager();
            $manager = $end_e->getManager();
            $manager->persist($inscription);
            $manager->flush();

            return $this->redirectToRoute('inscription_etudiants');
        }


        return $this->render('universg/inscription_etudiants.html.twig', [
            'controller_name' => 'EtudiantsController',
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'filieres'=>$filiereRepository->filieresUser($user),
            'etudiants'=>$etudiantRepository->etudiantsUserPasInscris($user)
        ]);
    }

    /**
     * @Route("liste_inscriptions_etudiants", name="liste_inscriptions_etudiants")
     */
    public function liste_inscriptions_etudiants( FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,Request $request, InscriptionRepository $repo)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on consulte les données
        $repostn=$this->getDoctrine()->getRepository(Ue::class)->findAll();
        $search=$repo->search($request->request->get('niveau'),$request->request->get('filiere'));
        return $this->render('universg/liste_inscriptions_etudiants.html.twig', [
            'controller_name' => 'EtudiantsController',
            'ues'=>$repostn,
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'recherche'=>$search
        ]);
    }
    
}
