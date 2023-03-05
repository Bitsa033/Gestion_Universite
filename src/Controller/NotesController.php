<?php

namespace App\Controller;
use App\Application\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class NotesController extends AbstractController
{
    /**
     * formulaire pour enregistrer les notes
     * @Route("notes_new_form", name="notes_new_form")
     */
    public function notes_new_form( SessionInterface $session,Application $application): Response
    {
        $user=$this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);

        $em=$application->repo_inscription->etudiantsMatieres($sessionF,$sessionN,$sessionSe);
        //dd($em);
       
       //dd($sessionCours);
        return $this->render('notes/notes.html.twig', [
            
            'filieres'=>$application->repo_filiere->findBy([
                'user'=>$user]),
            'niveaux' =>$application->repo_niveau->findBy([
                'user'=>$user]),
            'semestres' =>$application->repo_semestre->findAll(),
            'etudiants_et_matieres'=>$em
        ]);
    }

    /**
     * on enregistre les notes dans la bd
     * @Route("notes_new_save", name="notes_new_save")
     */
    public function notes_new_save(SessionInterface $session, Application $application)
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

                    $data=array(
                        'user'=>$user,
                        'inscription'=>$_POST['etudiant'][$key],
                        'cours'=>$_POST["cours"][$key],
                        'note'=>$_POST['moyenne'][$key],
                        'semestre'=>$sessionSe
                    );
                    
                    $application->new_note($data);
                    
                }
            }
            $this->addFlash('success', 'Enregistrement éffectué!');
        }
        return $this->redirectToRoute('notes_new_form');
    }

    /**
     * pour consulter les notes de l'étudiant on le choisi
     * on se dirige vers le template [notes_etudiant]
     * @Route("notes_etudiant", name="notes_etudiant")
     */
    function notes_etudiant(Application $application){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        return $this->render('notes/notes_etudiant.html.twig',[
            'filieres'=>$application->repo_filiere->findBy([
                'user'=>$user]),
            'semestres'=>$application->repo_semestre->findAll(),
            'niveaux'=>$application->repo_niveau->findBy([
                'user'=>$user]),
            
            'inscriptions'=>$application->repo_inscription->findAll(),
        ]);
    }

    /**
     * on affiche les notes d'un seul etudiant selon le semestre
     * @Route("releve_de_notes", name="releve_de_notes", methods={"GET"})
     */
    public function releve_de_notes(SessionInterface $session,Application $application): Response
    {
        $user = $this->getUser();

        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        $sessionInsc=$session->get('inscription');
        $noteE=$application->repo_note->notesEtudiant($user,$sessionInsc);
        
        return $this->render('notes/releve_de_notes.html.twig', [
            'filiere'=>$application->repo_filiere->find($sessionF),
            'niveau'=>$application->repo_niveau->find($sessionN),
            'semestre'=>$application->repo_semestre->find($sessionSe),
            'inscription'=>$application->repo_inscription->find($sessionInsc),
            'notes' => $noteE,
        ]);
    }

    /**
     * on affiche les notes de tous les etudiants de la filiere selon le semestre
     * @Route("notes_liste", name="notes_liste", methods={"GET"})
     */
    public function notes_liste(SessionInterface $session,Application $application): Response
    {
        $user = $this->getUser();

        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        $sessionInsc=$session->get('inscription');
        $noteE=$application->repo_note->notesEtudiant($user,$sessionInsc);
        
        return $this->render('notes/notes_liste.html.twig', [
            'filiere'=>$application->repo_filiere->find($sessionF),
            'niveau'=>$application->repo_niveau->find($sessionN),
            'semestre'=>$application->repo_semestre->find($sessionSe),
            'inscription'=>$application->repo_inscription->find($sessionInsc),
            'notes' => $noteE,
        ]);
    }

}
