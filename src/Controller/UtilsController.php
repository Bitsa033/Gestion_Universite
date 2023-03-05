<?php

namespace App\Controller;

use App\Application\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class UtilsController extends AbstractController
{
    /**
     * on enregistre l'ID de l'etudiant dans la session
     * pour afficher ses notes(route=note_liste)
     *  @Route("etudiant_session", name="etudiant_session")
     */
    public function etudiant_session(SessionInterface $session,Application $application)
    {
            $inscription = $application->repo_inscription->find($_POST['etudiantId']);
            if (!empty($inscription)) {
                $inscription_session = $session->get('inscription', []);
                if (!empty($inscription_session)) {
                    $session->set('inscription', $inscription);
                }
                $session->set('inscription', $inscription);
            }
            return $this->redirectToRoute('releve_de_notes');
        
    }

    /**
     * fns(filiere,niveau,semestre) 
     * on enregistre l'ID de la filiere,le niveau,le semestre dans la session
     * pour enregistrer des cours(route=matieres_transfert_form)
     * @Route("matieres_session_fns", name="matieres_session_fns")
     */
    public function matieres_session_fns(Request $request, SessionInterface $session, Application $application)
    {
        
        $filiere = $application->repo_filiere->find($request->request->get("filiere"));
        $niveau = $application->repo_niveau->find($request->request->get('niveau'));
        $semestre=$application->repo_semestre->find($request->request->get('semestre'));

        if (!empty($filiere) && !empty($niveau) && !empty($semestre)) {
            $filiere_session = $session->get('filiere', []);
            $niveau_session = $session->get('niveau', []);
            $semestre_session = $session->get('semestre', []);
            if (!empty($filiere_session) && !empty($niveau_session) && !empty($semestre_session)) {
                $session->set('filiere', $filiere);
                $session->set('niveau', $niveau);
                $session->set('semestre', $semestre);
            }
            $session->set('filiere', $filiere);
            $session->set('niveau', $niveau);
            $session->set('semestre', $semestre);
            
        }

        if ($request->request->get('matieres_transert_form')) {
            //dd('nouveaux cours');
            return $this->redirectToRoute('matieres_transert_form');
        } 
        elseif ($request->request->get('notes_new_form')) {
            return $this->redirectToRoute('notes_new_form');
        }
         
        else{
            dd('aucun choix');
        }
        

    }

}
