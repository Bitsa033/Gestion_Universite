<?php

namespace App\Controller;

use App\Entity\NotesEtudiant;
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
 * @Route("notes", name="notes_")
 */
class NotesController extends AbstractController
{

    /**
     * @Route("index", name="notesIndividuelles")
     */
    public function index(NotesEtudiantRepository $notesEtudiantRepository,UeRepository $matiereRepository,InscriptionRepository $etudiantRepository): Response
    {
        return $this->render('notes/index.html.twig', [
            'etudiant' => $etudiantRepository->find(1),
            'matieres'=>$notesEtudiantRepository->findBy(
                ['inscription'=>1,
            ]),
            'notes' => $notesEtudiantRepository->findBy(
                ['inscription'=>1,
            ]),
        ]);
    }

    /**
     * @Route("notesC", name="notesCollectives")
     */
    public function notesC(InscriptionRepository $inscriptionRepository,UeRepository $ueRepository,NotesEtudiantRepository $notesEtudiantRepository): Response
    {
        $user=$this->getUser();
        // $rep=$inscriptionRepository->find(1);

        return $this->render('notes/notesC.html.twig', [
            'notes'=>$notesEtudiantRepository->notesEtudiant($user),
            'etudiant1' => $inscriptionRepository->findBy([
                'filiere'=>4,
                'niveau'=>1,
                'etudiant'=>1
            ]),
            'etudiant2' => $inscriptionRepository->findBy([
                'filiere'=>4,
                'niveau'=>1,
                'etudiant'=>2
            ]),
            'etudiant3' => $inscriptionRepository->findBy([
                'filiere'=>4,
                'niveau'=>1,
                'etudiant'=>4
            ]),
            'matieres'=>$ueRepository->findBy(
                ['filiere'=>4,
                 'niveau'=>1
            ]),
            'notes1' => $notesEtudiantRepository->findBy([
                'inscription'=>1
            ]),
            'notes2' => $notesEtudiantRepository->findBy([
                'inscription'=>2
            ]),
            'notes3' => $notesEtudiantRepository->findBy([
                'inscription'=>3
            ]),
        
        ]);
    }

    /**
     * on se dirige vers le template [passerelleNotes]
     * @Route("passerelleNotes", name="passerelleNotes")
     */
    function passerelleNotes(SessionInterface $session,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, SemestreRepository $semestreRepository,UeRepository $ueRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);

        return $this->render('notes_etudiant/passerelleNotes.html.twig',[
            'filieres'=>$filiereRepository->filieresUser($user),
            'semestres'=>$semestreRepository->findAll(),
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'cours' =>  $ueRepository->uesFiliereNiveau($sessionF, $sessionN,$sessionSe),
        ]);
    }

    /**
     * on se dirige vers le template [passerelleEtudiants]
     * @Route("passerelleEtudiants", name="passerelleEtudiants")
     */
    function passerelleEtudiants(SessionInterface $session, FiliereRepository $filiereRepository,NiveauRepository $niveauRepository,SemestreRepository $semestreRepository, InscriptionRepository $inscriptionRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);

        return $this->render('notes_etudiant/passerelleEtudiant.html.twig',[
            'filieres'=>$filiereRepository->filieresUser($user),
            'semestres'=>$semestreRepository->findAll(),
            'classes'=>$niveauRepository->niveauxUser($user),
            
            'inscriptions2'=>$inscriptionRepository->inscriptionsUserFiliereNiveau($user,$sessionF,$sessionN),
        ]);
    }

    /**
     * on traite le template [passerelleEtudiant]
     * @Route("choixEtudiant", name="choixEtudiant")
     */
    public function choixEtudiant(SessionInterface $session, InscriptionRepository $inscriptionRepository)
    {

        if (isset($_POST['enregistrer'])) {
            $inscription = $inscriptionRepository->find($_POST['etudiantId']);
            $get_filiere = $session->get('filiere', []);
            $get_inscription= $session->get('niveau', []);
            $get_semestre = $session->get('semestre', []);
            if (!empty($get_inscription)) {
                $session->set('inscription', $inscription);
            }
            $session->set('inscription', $inscription);
            //dd($session);

            //return $this->redirectToRoute('etudiants_i');
        }

        return $this->redirectToRoute('notes_etudiant_index');
    }

    /**
     * on traite le template [passerelleNotes]
     * @Route("choixFiliereNiveauxSemestreN", name="choixFiliereNiveauxSemestreN")
     */
    public function choixFiliereNiveauxSemestreN(Request $request, SessionInterface $session, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,SemestreRepository $semestreRepository)
    {

        if (!empty($request->request->get('filiere')) && !empty($request->request->get('classe')) && !empty($request->request->get('semestre'))) {
            $filiere = $filiereRepository->find($request->request->get("filiere"));
            $classe = $niveauRepository->find($request->request->get('classe'));
            $semestre=$semestreRepository->find($request->request->get('semestre'));
            $get_filiere = $session->get('filiere', []);
            $get_classe = $session->get('niveau', []);
            $get_semestre = $session->get('semestre', []);
            if (!empty($get_filiere) && !empty($get_classe) && !empty($get_semestre)) {
                $session->set('filiere', $filiere);
                $session->set('niveau', $classe);
                $session->set('semestre', $semestre);
            }
            $session->set('filiere', $filiere);
            $session->set('niveau', $classe);
            $session->set('semestre', $semestre);
            //dd($session);

            //return $this->redirectToRoute('etudiants_i');
        }

        return $this->redirectToRoute('notes_passerelleNotes');
    }

    /**
     * on traite la route qui se trouve  dans le template [notes_etudiant/essaie]
     * @Route("choixFiliereNiveauxSemestreNc", name="choixFiliereNiveauxSemestreNc")
     */
    public function choixFiliereNiveauxSemestreNc(Request $request, SessionInterface $session, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,SemestreRepository $semestreRepository)
    {

        if (!empty($request->request->get('filiere')) && !empty($request->request->get('classe')) && !empty($request->request->get('semestre'))) {
            $filiere = $filiereRepository->find($request->request->get("filiere"));
            $classe = $niveauRepository->find($request->request->get('classe'));
            $semestre=$semestreRepository->find($request->request->get('semestre'));
            $get_filiere = $session->get('filiere', []);
            $get_classe = $session->get('niveau', []);
            $get_semestre = $session->get('semestre', []);
            if (!empty($get_filiere) && !empty($get_classe) && !empty($get_semestre)) {
                $session->set('filiere', $filiere);
                $session->set('niveau', $classe);
                $session->set('semestre', $semestre);
            }
            $session->set('filiere', $filiere);
            $session->set('niveau', $classe);
            $session->set('semestre', $semestre);
            //dd($session);

            //return $this->redirectToRoute('etudiants_i');
        }

        return $this->redirectToRoute('notes_s');
    }

    /**
     * @Route("sessionCours", name="sessionCours")
     */
    public function sessionCours(SessionInterface $session, Request $request,UeRepository $ueRepository)
    {
        if (!empty($request->request->get('cours'))) {
            $cours = $ueRepository->find($request->request->get('cours'));
            $get_cours = $session->get('cours', []);
            if (!empty($get_cours)) {
                $session->set('cours', $cours);
            }
            $session->set('cours', $cours);
            //dd($session);
        }
       return $this->redirectToRoute('notes_s');
    }

    /**
     * @Route("s", name="s")
     */
    public function Note(ManagerRegistry $managerRegistry, SessionInterface $session, InscriptionRepository $inscriptionRepository, UeRepository $ueRepository,FiliereRepository $filiereRepository,NiveauRepository $niveauRepository, SemestreRepository $semestreRepository): Response
    {
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        $sessionCours = $session->get('cours', []);
        $user = $this->getUser();

        if (!empty($sessionCours) && isset($_POST['enregistrer'])) {

            $check_array = $_POST['inscription'];
            foreach ($_POST['nom'] as $key => $value) {
                if (in_array($_POST['nom'][$key], $check_array)) {
                    //dd($request->request->get("inscription")[$key]);
                    //echo $request->request->get("moyenne")[$key];
                    //echo '<br>';
                    $etudiant = $inscriptionRepository->find($_POST['nom'][$key]);
                    $cours = $ueRepository->find($sessionCours);
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
        }
       
       //dd($sessionCours);
        return $this->render('notes_etudiant/essaie.html.twig', [
            'cours' =>  $ueRepository->uesFiliereNiveau($sessionF, $sessionN,$sessionSe),
            'inscriptions' =>$inscriptionRepository->EtudiantPasDeNote($user,$sessionF,$sessionN,$sessionSe,$sessionCours),
            'filieres'=>$filiereRepository->filieresUser($user),
            'classes' =>$niveauRepository->niveauxUser($user),
            'semestres' =>$semestreRepository->findAll(),
        ]);
    }
}
