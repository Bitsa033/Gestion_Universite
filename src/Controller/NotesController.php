<?php

namespace App\Controller;

use App\Repository\FiliereRepository;
use App\Repository\InscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\NiveauRepository;
use App\Repository\NotesEtudiantRepository;
use App\Repository\SemestreRepository;
use App\Repository\UeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Enregistrement\EcritureNote;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("notes_", name="notes_")
 */
class NotesController extends AbstractController
{

    // /**
    //  * on se dirige vers le template [passerelleNotes]
    //  * @Route("passerelleNotes", name="passerelleNotes")
    //  */
    // function passerelleNotes(SessionInterface $session,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, SemestreRepository $semestreRepository,UeRepository $ueRepository,InscriptionRepository $inscriptionRepository){
    //     //on cherche l'utilisateur connecté
    //     $user= $this->getUser();
    //     if (!$user) {
    //       return $this->redirectToRoute('app_login');
    //     }

    //     $sessionF = $session->get('filiere', []);
    //     $sessionN = $session->get('niveau', []);
    //     $sessionSe = $session->get('semestre', []);
        

    //     return $this->render('notes_etudiant/passerelleNotes.html.twig',[
    //         'filieres'=>$filiereRepository->findBy([
    //             'user'=>$user
    //         ]),
    //         'semestres'=>$semestreRepository->findAll(),
    //         'niveaux'=>$niveauRepository->findBy([
    //             'user'=>$user
    //         ]),
    //         'cours' =>  $ueRepository->findBy([
    //             'filiere'=>$sessionF, 
    //             'niveau'=>$sessionN,
    //             'semestre'=>$sessionSe
    //         ]),
            
    //     ]);
    // }


    // /**
    //  * on traite le template [passerelleNotes]
    //  * @Route("choixFiliereNiveauxSemestreN", name="choixFiliereNiveauxSemestreN")
    //  */
    // public function choixFiliereNiveauxSemestreN(Request $request, SessionInterface $session, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,SemestreRepository $semestreRepository)
    // {

    //     if (!empty($request->request->get('filiere')) && !empty($request->request->get('classe')) && !empty($request->request->get('semestre'))) {
    //         $filiere = $filiereRepository->find($request->request->get("filiere"));
    //         $classe = $niveauRepository->find($request->request->get('classe'));
    //         $semestre=$semestreRepository->find($request->request->get('semestre'));
    //         $get_filiere = $session->get('filiere', []);
    //         $get_classe = $session->get('niveau', []);
    //         $get_semestre = $session->get('semestre', []);
    //         if (!empty($get_filiere) && !empty($get_classe) && !empty($get_semestre)) {
    //             $session->set('filiere', $filiere);
    //             $session->set('niveau', $classe);
    //             $session->set('semestre', $semestre);
    //         }
    //         $session->set('filiere', $filiere);
    //         $session->set('niveau', $classe);
    //         $session->set('semestre', $semestre);
    //         //dd($session);

    //         //return $this->redirectToRoute('etudiants_i');
    //     }

    //     return $this->redirectToRoute('notes_s');
    // }

    // /**
    //  * @Route("sessionCours", name="sessionCours")
    //  */
    // public function sessionCours(SessionInterface $session, Request $request,UeRepository $ueRepository)
    // {
    //     if (!empty($request->request->get('cours'))) {
    //         $cours = $ueRepository->find($request->request->get('cours'));
    //         $get_cours = $session->get('cours', []);
    //         if (!empty($get_cours)) {
    //             $session->set('cours', $cours);
    //         }
    //         $session->set('cours', $cours);
    //         //dd($session);
    //     }
    //    return $this->redirectToRoute('notes_s');
    // }

    /**
     *  filiere_et_classe pour le formulaire des notes [formulaire_notes]
     * @Route("filiere_et_classe", name="filiere_et_classe")
     */
    public function filiere_et_classe(Request $request, SessionInterface $session, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,SemestreRepository $semestreRepository)
    {
        $get_filiere = $session->get('filiere', []);
        $get_classe = $session->get('niveau', []);
        $get_semestre = $session->get('semestre', []);

        $filiere_post=$request->request->get('filiere');
        $niveau_post=$request->request->get('classe');
        $semestre_post=$request->request->get('semestre');

        if (!empty($filiere_post) && !empty($niveau_post) && !empty($semestre_post)) {
            $filiere = $filiereRepository->find($filiere_post);
            $classe = $niveauRepository->find($niveau_post);
            $semestre=$semestreRepository->find($semestre_post);
            
            if (!empty($get_filiere) && !empty($get_classe) && !empty($get_semestre)) {
                $session->set('filiere', $filiere);
                $session->set('niveau', $classe);
                $session->set('semestre', $semestre);
            }
            $session->set('filiere', $filiere);
            $session->set('niveau', $classe);
            $session->set('semestre', $semestre);
            
        }

        return $this->redirectToRoute('notes_formulaire_notes');
    }

    /**
     * formulaire pour enregistrer les notes
     * @Route("formulaire_notes", name="formulaire_notes")
     */
    public function Note( SessionInterface $session, InscriptionRepository $inscriptionRepository,FiliereRepository $filiereRepository,NiveauRepository $niveauRepository, SemestreRepository $semestreRepository): Response
    {
        $user=$this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        if (!empty($sessionF)) {
            $repfiliere=$filiereRepository->find($sessionF);
            
        }
        else {
            $repfiliere=null;
        }
        //classe
        if (!empty($sessionN)) {
            $repclasse=$niveauRepository->find($sessionN);
            
        }
        else {
            $repclasse=null;
        }
        //semestre
        if (!empty($sessionSe)) {
            $repsemestre=$semestreRepository->find($sessionSe);
            
        }
        else {
            $repsemestre=null;
        }
        $user = $this->getUser();

        $em=$inscriptionRepository->etudiantsMatieres($repfiliere,$repclasse,$repsemestre);
        //dd($em);
       
       //dd($sessionCours);
        return $this->render('notes_etudiant/notes.html.twig', [
            
            'filieres'=>$filiereRepository->findBy([
                'user'=>$user]),
            'classes' =>$niveauRepository->findBy([
                'user'=>$user]),
            'semestres' =>$semestreRepository->findAll(),
            'etudiantsMatieres'=>$em
        ]);
    }

    /**
     * enregistrement des notes
     * @Route("actionNotes", name="actionNotes")
     */
    public function actionNotes(SessionInterface $session, InscriptionRepository $inscriptionRepository,
    SemestreRepository $semestreRepository,UeRepository $ueRepository, ManagerRegistry $managerRegistry)
    {
        $user=$this->getUser();
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        // $sessionCours = $session->get('cours', []);
        if (!empty($sessionF) && !empty($sessionN) && !empty($sessionSe)
         && isset($_POST['enregistrer'])) {

            $check_array = $_POST['inscription'];
            foreach ($_POST['etudiant'] as $key => $value) {
                if (in_array($_POST['inscription'][$key], $check_array)) {
                    echo $_POST["inscription"][$key];
                    echo $_POST["moyenne"][$key];
                    echo '<br>';
                    //echo $request->request->get("moyenne")[$key];
                    //echo '<br>';
                    $etudiant = $inscriptionRepository->find($_POST['etudiant'][$key]);
                    $cours = $ueRepository->find($_POST["cours"][$key]);
                    $moyenne = $_POST['moyenne'][$key];
                    $data=array(
                        'moyenne'=>$moyenne
                    );
                    $semestre = $semestreRepository->find($sessionSe);
                    //dd($session);
                    $notesEtudiant = new EcritureNote();
                    $notesEtudiant->Enregistrer($data,$etudiant,$cours,$semestre,$user,$managerRegistry);
                    
                }
            }
            // $this->addFlash('success', 'Enregistrement éffectué!');
        }
        return $this->redirectToRoute('notes_formulaire_notes');
    }

    /**
     * on se dirige vers le template [listeEtudiants]
     * @Route("listeEtudiants", name="listeEtudiants")
     */
    function listeEtudiants(SessionInterface $session, FiliereRepository $filiereRepository,NiveauRepository $niveauRepository,SemestreRepository $semestreRepository, InscriptionRepository $inscriptionRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);

        return $this->render('notes_etudiant/listeEtudiants.html.twig',[
            'filieres'=>$filiereRepository->findBy([
                'user'=>$user]),
            'semestres'=>$semestreRepository->findAll(),
            'classes'=>$niveauRepository->findBy([
                'user'=>$user]),
            
            'inscriptions'=>$inscriptionRepository->findAll(),
        ]);
    }

    /**
     * on traite le template [notes_etudiants/passerelleEtudiant]
     * @Route("actionListeEtudiants", name="actionListeEtudiants")
     */
    public function actionListeEtudiants(SessionInterface $session, InscriptionRepository $inscriptionRepository)
    {

        if (isset($_POST['enregistrer'])) {
            $inscription = $inscriptionRepository->find($_POST['etudiantId']);
            $get_inscription= $session->get('inscription', []);
            if (!empty($get_inscription)) {
                $session->set('inscription', $inscription);
            }
            $session->set('inscription', $inscription);
            
        }

        return $this->redirectToRoute('notes_etudiant_index');
    }
}
