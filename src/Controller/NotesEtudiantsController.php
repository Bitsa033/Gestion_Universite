<?php

namespace App\Controller;

use App\Entity\NotesEtudiant;
use App\Repository\UeRepository;
use App\Repository\NiveauRepository;
use App\Repository\FiliereRepository;
use App\Repository\InscriptionRepository;
use Doctrine\Persistence\ManagerRegistry;
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
     * @Route("choix_filiereEtNiveauNotes_liste", name="choix_filiereEtNiveauNotes_liste")
     */
    public function index(): Response
    {
        return $this->render('notes_etudiants/index.html.twig', [
            'controller_name' => 'NotesEtudiantsController',
        ]);
    }

    /**
     * on choisi la filiere et le niveau de l'etudiant a noter
     * @Route("filiereEtNiveauu_etudiant", name="filiereEtNiveauu_etudiant")
     */
    function filiere_niveau_etudiant(SessionInterface $session, Request $request,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

       if (!empty($request->request->all())) {
           $filiere=$filiereRepository->find($request->request->get("filiereNote"));
           $niveau=$niveauRepository->find($request->request->get('niveauNote'));
           $get_filiere=$session->get('filiereNote',[]);
           if (!empty($get_filiere)) {
               $session->set('filiereNote',$filiere);
               $session->set('niveauNote',$niveau);
           }
           //dd($session);
           $session->set('filiereNote',$filiere);
           $session->set('niveauNote',$niveau);
           
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
    function choixEtudiant_notes(SessionInterface $session, Request $request, InscriptionRepository $inscriptionRepository, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        if (!empty($request->request->all())) {
            $inscription= $inscriptionRepository->find($request->request->get("inscription"));
            $get_inscription=$session->get('inscriptionNote',[]);
            if (!empty($get_inscription)) {
                $session->set('inscriptionNote',$inscription);
            }
            //dd($session);
            $session->set('inscriptionNote',$inscription);
            
            return $this->redirectToRoute('notes_etudiants_ajout_notes');
        }

        $sessionF=$session->get('filiereNote',[]);
        $sessionN=$session->get('niveauNote',[]);

        $filiere=$filiereRepository->find($sessionF);
        $niveau=$niveauRepository->find($sessionN);
        return $this->render('notes_etudiants/choixEtudiant_notes.html.twig',[
            'inscriptions'=>$inscriptionRepository->inscriptionsUserFiliereNiveau($user,$filiere,$niveau),
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
        $sessionI=$session->get('inscriptionNote',[]);
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
            return $this->redirectToRoute('etudiants_ajout_notes');
        }

        $sessionF=$session->get('filiereNote',[]);
        $sessionN=$session->get('niveauNote',[]);

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
