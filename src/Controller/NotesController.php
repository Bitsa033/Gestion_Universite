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
use PDO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("notes_", name="notes_")
 */
class NotesController extends AbstractController
{
    /**
     * @Route("notesC", name="notesCollectives")
     */
    public function notesC(InscriptionRepository $inscriptionRepository,UeRepository $ueRepository,NotesEtudiantRepository $notesEtudiantRepository): Response
    {
        $user=$this->getUser();

        $pdo= $this->getDoctrine()->getConnection();
        $sql="select e.nom as etudiant,moyenne,m.nom as matiere
        from notes_etudiant n inner join inscription i on 
        i.id=n.inscription_id inner join ue on
        ue.id=n.ue_id inner join matiere m on m.id=ue.matiere_id
        inner join etudiant e on e.id=i.etudiant_id
        limit 0,8

        ";
        $prepare=$pdo->prepare($sql);
        $prepare->execute();
        $result=$prepare->fetchAll();
        //dd($result);
        
        return $this->render('notes/notesC.html.twig', [
            'notes'=>$result,
            
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
            'filieres'=>$filiereRepository->findBy([
                'user'=>$user]),
            'semestres'=>$semestreRepository->findAll(),
            'niveaux'=>$niveauRepository->findBy([
                'user'=>$user]),
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
            'filieres'=>$filiereRepository->findBy([
                'user'=>$user]),
            'semestres'=>$semestreRepository->findAll(),
            'classes'=>$niveauRepository->findBy([
                'user'=>$user]),
            
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
            $this->addFlash('success', 'Enregistrement éffectué!');
        }
       
       //dd($sessionCours);
        return $this->render('notes_etudiant/notes.html.twig', [
            'cours' =>  $ueRepository->uesFiliereNiveau($sessionF, $sessionN,$sessionSe),
            'inscriptions' =>$inscriptionRepository->EtudiantPasDeNote($user,$sessionF,$sessionN,$sessionSe,$sessionCours),
            'filieres'=>$filiereRepository->findBy([
                'user'=>$user]),
            'classes' =>$niveauRepository->findBy([
                'user'=>$user]),
            'semestres' =>$semestreRepository->findAll(),
        ]);
    }
}
