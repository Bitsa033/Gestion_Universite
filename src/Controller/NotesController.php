<?php

namespace App\Controller;
use App\Application\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class NotesController extends AbstractController
{
    /**
     * formulaire pour enregistrer les notes
     * @Route("nouvelle_note", name="nouvelle_note")
     */
    public function nouvelle_note(Request $request, SessionInterface $session,Application $application): Response
    {
        $user=$this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $filiere=$session->get('filiere');
        $niveau=$session->get('niveau');
        $semestre=$session->get('semestre');
        
        return $this->render('notes/nouvelle.html.twig', [
            
            'filieres'=>$application->repo_filiere->findBy([
                'user'=>$user]),
            'niveaux' =>$application->repo_niveau->findBy([
                'user'=>$user]),
            'semestres' =>$application->repo_semestre->findAll(),
            'liste_etudiant_matiere'=>$application->repo_inscription->liste_etudiant_matiere($filiere,$niveau,$semestre),
            
        ]);
    }

    /**
     * on enregistre les notes dans la bd
     * @Route("nouvelle_note_traitement", name="nouvelle_note_traitement")
     */
    public function nouvelle_note_traitement(SessionInterface $session, Application $application)
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
                    
                    $application->nouvelle_note($data);
                    
                }
            }
            $this->addFlash('success', 'Enregistrement éffectué!');
        }
        return $this->redirectToRoute('nouvelle_note');
    }

    /**
     * pour consulter les notes de l'étudiant on le choisi
     * on se dirige vers le template [porte_releve_de_notes]
     * @Route("porte_releve_de_notes", name="porte_releve_de_notes")
     */
    function porte_releve_de_notes(Application $application){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        return $this->render('notes/porte_releve_de_notes.html.twig',[
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

        $filiere=$application->repo_filiere->find($sessionF);
        $niveau=$application->repo_niveau->find($sessionN);
        $semestre=$application->repo_semestre->find($sessionSe);
        $etudiant=$application->repo_inscription->find($sessionInsc);
        $noteE=$application->repo_note->findBy([
            'user'=>$user,
            'inscription'=>$etudiant,
            'semestre'=>$sessionSe
        ]);
        
        return $this->render('notes/releve_de_notes.html.twig', [
            'filiere'=>$filiere,
            'niveau'=>$niveau,
            'semestre'=>$semestre,
            'inscription'=>$etudiant,
            'notes' => $noteE,
        ]);
    }

    /**
     * on affiche les notes de tous les etudiants de la filiere selon le semestre
     * @Route("liste_note", name="liste_note", methods={"GET"})
     */
    public function liste_note(SessionInterface $session,Application $application): Response
    {
        $user = $this->getUser();
        // dd('liste des notes');

        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        $sessionInsc=$session->get('inscription');
        $noteE=$application->repo_note->findAll();
        
        return $this->render('notes/liste_note.html.twig', [
            'filiere'=>$application->repo_filiere->find($sessionF),
            'niveau'=>$application->repo_niveau->find($sessionN),
            'semestre'=>$application->repo_semestre->find($sessionSe),
            'inscription'=>$application->repo_inscription->find($sessionInsc),
            'notes' => $noteE,
        ]);
    }

}
