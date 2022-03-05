<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\NotesEtudiant;
use App\Repository\UeRepository;
use App\Repository\NiveauRepository;
use App\Repository\FiliereRepository;
use App\Repository\InscriptionRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\NotesEtudiantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("notes_etudiants_", name="notes_etudiants_")
 */
class NotesEtudiantsController extends AbstractController
{

    /**
     * on choisi la filiere et le niveau de l'etudiant a noter
     * @Route("choixFiliereEtNiveau_listeNotes", name="choixFiliereEtNiveau_listeNotes")
     */
    function choixFiliereEtNiveau_listeNotes(SessionInterface $session,Request $request,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        if (!empty($request->request->all())) {
            $filiere=$filiereRepository->find($request->request->get("filiere"));
            $niveau=$niveauRepository->find($request->request->get('niveau'));
            $get_filiere=$session->get('filiere',[]);
            if (!empty($get_filiere)) {
              $session->set('filiere',$filiere);
              $session->set('niveau',$niveau);
            }
            //dd($session);
            $session->set('filiere',$filiere);
            $session->set('niveau',$niveau);
            return $this->redirectToRoute('notes_etudiants_liste');
        }
        return $this->render('notes_etudiants/choixFiliereEtNiveau_listeNotes.html.twig',[
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * @Route("liste", name="liste")
     */
    public function index(SessionInterface $session,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, NotesEtudiantRepository $notesEtudiantRepository): Response
    {
        $user=$this->getUser();
        $filiere=$filiereRepository->find($session->get('filiere',[]));
        $niveau=$niveauRepository->find($session->get('niveau',[]));

        return $this->render('notes_etudiants/index.html.twig', [
            'controller_name' => 'NotesEtudiantsController',
            'notes'=>$notesEtudiantRepository->notesEtudiantUser($user,$filiere,$niveau),
            'filiere'=>$filiere,
            'niveau'=>$niveau
        ]);
    }

    /**
     * on choisi la filiere et le niveau de l'etudiant a noter
     * @Route("filiereEtNiveau_etudiant", name="filiereEtNiveau_etudiant")
     */
    function filiere_niveau_etudiant(SessionInterface $session,Request $request,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        if (!empty($request->request->all())) {
            $filiere=$filiereRepository->find($request->request->get("filiere"));
            $niveau=$niveauRepository->find($request->request->get('niveau'));
            $get_filiere=$session->get('filiere',[]);
            if (!empty($get_filiere)) {
              $session->set('filiere',$filiere);
              $session->set('niveau',$niveau);
            }
            //dd($session);
            $session->set('filiere',$filiere);
            $session->set('niveau',$niveau);
            return $this->redirectToRoute('notes_etudiants_choixEtudiant_notes');
        }
        return $this->render('notes_etudiants/filiereEtNiveau_etudiant.html.twig',[
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * on choisi l'etudiant a noter
     * @Route("choixEtudiant_notes", name="choixEtudiant_notes")
     */
    function choixEtudiant_notes(SessionInterface $session,Request $request,InscriptionRepository $inscriptionRepository,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository){
       //on cherche l'utilisateur connecté
       $user= $this->getUser();
       //si l'utilisateur est n'est pas connecté,
       // on le redirige vers la page de connexion
       if (!$user) {
         return $this->redirectToRoute('app_login');
       }

       if (!empty($request->request->all())) {
           $inscription=$inscriptionRepository->find($request->request->get("inscription"));
           $get_inscription=$session->get('inscription',[]);
           if (!empty($get_filiere)) {
             $session->set('inscription',$inscription);
           }
           //dd($session);
           $session->set('inscription',$inscription);
           return $this->redirectToRoute('notes_etudiants_ajout_notes');
       }
       $sessionF=$session->get('filiere',[]);
        $sessionN=$session->get('niveau',[]);
        $inscriptions=$inscriptionRepository->inscriptionsUserFiliereNiveau($user,$sessionF,$sessionN);
        return $this->render('notes_etudiants/choixEtudiant_notes.html.twig',[
            'inscriptions'=>$inscriptions
        ]);
    }

    /**
     * on cree des notes pour l'etudiant
     * @Route("ajout_notes", name="ajout_notes")
     */
    public function ajout_notes( SessionInterface $session,InscriptionRepository $inscriptionRepository, UeRepository $ueRepository,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,Request $request,ManagerRegistry $end_e )
        {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on insert les données
        $sessionI=$session->get('inscription',[]);
        if ( !empty($sessionI) && !empty($request->request->get('moyenne'))) {
           
            $ue = $ueRepository->find($request->request->get("ue"));
            $inscription = $inscriptionRepository->find($sessionI);

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
            return $this->redirectToRoute('notes_etudiants_ajout_notes');
        }

        $sessionF=$session->get('filiere',[]);
        $sessionN=$session->get('niveau',[]);
        $sessionI=$session->get('inscription',[]);
        $iu=$inscriptionRepository->find($sessionI);
        //$matieres=$ueRepository->uesFiliereNiveau($sessionF,$sessionN);
        $matieres=$ueRepository->uePasEncoreNoterPourInscription($user,$sessionF,$sessionN,$sessionI);
        $inscriptions=$inscriptionRepository->inscriptionsUserFiliereNiveau($user,$sessionF,$sessionN);

        return $this->render('notes_etudiants/ajoutNotes_etudiant.html.twig', [
            'controller_name' => 'UniversgController',
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'matieres'=>$matieres,
            'inscriptions'=>$inscriptions
        ]);
    }
}
