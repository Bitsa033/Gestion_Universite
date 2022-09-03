<?php

namespace App\Controller;

use App\Repository\EtudiantRepository;
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

        $em=$inscriptionRepository->etudiantsMatieres($repfiliere,$repclasse,$repsemestre);
        //dd($em);
       
       //dd($sessionCours);
        return $this->render('notes/notes.html.twig', [
            
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

        if (!empty($sessionF) && !empty($sessionN) && !empty($sessionSe)
         && isset($_POST['enregistrer'])) {

            $check_array = $_POST['inscription'];
            foreach ($_POST['etudiant'] as $key => $value) {
                if (in_array($_POST['inscription'][$key], $check_array)) {
                    $inscr= $_POST["inscription"][$key];
                    $moy= $_POST["moyenne"][$key];
                    //echo '<br>';
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
    function listeEtudiants(FiliereRepository $filiereRepository,NiveauRepository $niveauRepository,SemestreRepository $semestreRepository, InscriptionRepository $inscriptionRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        return $this->render('notes/listeEtudiants.html.twig',[
            'filieres'=>$filiereRepository->findBy([
                'user'=>$user]),
            'semestres'=>$semestreRepository->findAll(),
            'classes'=>$niveauRepository->findBy([
                'user'=>$user]),
            
            'inscriptions'=>$inscriptionRepository->findAll(),
        ]);
    }

    /**
     * on traite le template [notes/listeEtudiants]
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

        return $this->redirectToRoute('notes_notes_index');
    }

    /**
     * @Route("index", name="notes_index", methods={"GET"})
     */
    public function index(EtudiantRepository $etudiantRepository,SessionInterface $session,InscriptionRepository $inscriptionRepository, NotesEtudiantRepository $notesEtudiantRepository,UeRepository $cours, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, SemestreRepository $semestreRepository ): Response
    {
        $user = $this->getUser();

        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        $sessionInsc=$session->get('inscription');
        $noteE=$notesEtudiantRepository->notesEtudiant($user,$sessionInsc);

        
        return $this->render('notes/index.html.twig', [
            'coursSemestre'=>$cours->findBy([
                'filiere'=>$sessionF,
                'niveau'=>$sessionN,
                'semestre'=>$sessionSe
            ]),
            'filiere'=>$filiereRepository->find($sessionF),
            'classe'=>$niveauRepository->find($sessionN),
            'semestre'=>$semestreRepository->find($sessionSe),
            'inscription'=>$inscriptionRepository->find($sessionInsc),
            'etudiant' => $etudiantRepository->findBy(['id'=>$sessionInsc]),
            'matieres'=>$notesEtudiantRepository->findBy(
                ['inscription'=>$sessionInsc,
            ]),
            'notes' => $noteE,
        ]);
    }

}
