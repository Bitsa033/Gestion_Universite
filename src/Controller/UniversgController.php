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

class UniversgController extends AbstractController
{
    /**
     * @Route("/universg", name="universg")
     */
    public function index(InscriptionRepository $inscriptionRepository, FiliereRepository $filiereRepository, NiveauRepository $NiveauRepository, MatiereRepository $matiereRepository) : Response
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
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$NiveauRepository->niveauxUser($user),
            'matieres'=>$matiereRepository->matieresUser($user)
        ]);
    }

    /**
     * Insertion et affichage des filieres
     * @Route("filieres", name="filieres")
     */
    public function filiere(FiliereRepository $filiereRepository,Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on insert les données
        if (!empty($request->request->get('nom_f')) && !empty($request->request->get('abbr_filiere'))) {
            $filiere=new Filiere();
            $filiere->setUser($user);
            $filiere->setNom(ucfirst($request->request->get('nom_f')));
            $filiere->setSigle(strtoupper($request->request->get('abbr_filiere')));
            $filiere->setCreatedAt(new \datetime);
            $manager = $end->getManager();
            $manager->persist($filiere);
            $manager->flush();

            return $this->redirectToRoute('filieres');
        } 
         
        return $this->render('universg/filieres.html.twig', [
            'controller_name' => 'UniversgController',
            'filieres'=>$filiereRepository->filieresUser($user),
            'filieresNb'=>$filiereRepository->findAll()
        ]);
    }

    /**
     * Suppression des filieres
     * @Route("filiere/suppression/{id}", name="suppression_filiere")
     */
    public function suppression_filiere (Filiere $filiere, ManagerRegistry $end)
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
        $manager->remove($filiere);
        $manager->flush();

        return $this->redirectToRoute('filieres');
        
        return $this->render('universg/filieres.html.twig', [
            'controller_name' => 'UniversgController'
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
            'controller_name' => 'UniversgController',
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'niveauxsNb'=>$niveauRepository->findAll(),
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
            'controller_name' => 'UniversgController'
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
            'controller_name' => 'UniversgController'
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
            'controller_name' => 'UniversgController',
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
            'controller_name' => 'UniversgController',
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
            'controller_name' => 'UniversgController',
            'ues'=>$repostn,
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'recherche'=>$search
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

    /**
     * Affectation des matieres à des filieres et niveaux
     * @Route("transfert_matiere", name="transfert_matiere")
     */
    public function transfert_matiere (FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,MatiereRepository $matiereRepository ,Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on insert les données
        if (!empty($request->request->get('niveau'))) {
            $filiere=$filiereRepository->find($request->request->get("filiere"));
            $niveau=$niveauRepository->find($request->request->get('niveau'));
            $matiere=$matiereRepository->find($request->request->get('ue'));
                
            $ue=new Ue();
            $ue->setFiliere($filiere);
            $ue->setUser($user);
            $ue->setNiveau($niveau);
            $ue->setMatiere($matiere);
            $ue->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($ue);
            $manager->flush();
            
            return $this->redirectToRoute('transfert_matiere');
        } 
       
        return $this->render('universg/transfert_matiere.html.twig', [
            'controller_name' => 'UniversgController',
            'filieres'=>$filiereRepository->filieresUser($user),
            'matieres'=>$matiereRepository->matieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * Affichage des unites d'enseignement
     * @Route("ue", name="ue")
     */
    public function ue (FiliereRepository $filiereRepository,NiveauRepository $niveauRepository ,Request $request, UeRepository $repo)
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
        $uesUserFiliereNiveau=$repo->uesFiliereNiveau($request->request->get('filiere'),$request->request->get('niveau'));
        return $this->render('universg/ue.html.twig', [
            'controller_name' => 'UniversgController',
            'ues'=>$repostn,
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'recherche'=>$uesUserFiliereNiveau
        ]);
    }


    /**
     * @Route("ue/suppression/{id}", name="suppression_ue")
     */
    public function suppression_ue(Ue $ue, ManagerRegistry $end)
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
        $manager->remove($ue);
        $manager->flush();

        return $this->redirectToRoute('ue');
        
    }

    /**
     * Creer des notes pour des etudiants
     * @Route("ajout_notes", name="ajout_notes")
     */
    public function ajout_notes( InscriptionRepository $inscriptionRepository, UeRepository $ueRepository,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,Request $request,ManagerRegistry $end_e )
        {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on insert les données
        if (!empty($request->request->get('moyenne'))) {
    
            $ue = $ueRepository->find($request->request->get("ue"));
            $inscription = $inscriptionRepository->find($request->request->get("inscription"));

            $note=new NotesEtudiant();
            $note->setUser($user);
            $note->setInscription($inscription);
            $note->setUe($ue);
            $note->setMoyenne($request->request->get('moyenne'));
            $note->setCreatedAt(new \DateTime());
            $manager=$end_e->getManager();
            $manager->persist($note);
            $manager->flush();

            //dd($request);
            return $this->redirectToRoute('ajout_notes');
        }

        $matieres=$ueRepository->uesFiliereNiveau($request->request->get('filiere'),$request->request->get('niveau'));
        $inscriptions=$inscriptionRepository->inscriptionsUserFiliereNiveau($user,$request->request->get('filiere'),$request->request->get('niveau'));

        return $this->render('universg/noter_etudiant.html.twig', [
            'controller_name' => 'UniversgController',
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'matieres'=>$matieres,
            'inscriptions'=>$inscriptions
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

