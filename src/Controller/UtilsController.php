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
     * @Route("porte_inscription_etudiant_traitement", name="porte_inscription_etudiant_traitement")
     */
    public function porte_inscription_etudiant_traitement(Request $request, SessionInterface $session, Application $application)
    {

        if (!empty($request->request->get('filiere')) && !empty($request->request->get('niveuau'))) {
            $filiere = $application->repo_filiere->find($request->request->get("filiere"));
            $niveau = $application->repo_niveau->find($request->request->get('niveau'));
            $session_filiere = $session->get('filiere', []);
            $session_niveau = $session->get('niveau', []);
            if (!empty($session_filiere && $session_niveau)) {
                $session->set('filiere', $filiere);
                $session->set('niveau', $niveau);
            }
            $session->set('filiere', $filiere);
            $session->set('niveau', $niveau);
            //dd($session);

            //return $this->redirectToRoute('etudiants_i');
        }

        return $this->redirectToRoute('inscription_etudiant');
    }
    
    /**
     * fns(filiere,niveau,semestre) 
     * on enregistre l'ID de la filiere,le niveau,le semestre dans la session
     * pour enregistrer des cours(route=nouveau_cour)
     * @Route("porte_nouveau_cour_traitement", name="porte_nouveau_cour_traitement")
     */
    public function porte_nouveau_cour_traitement(Request $request, SessionInterface $session, Application $application)
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
        
        return $this->redirectToRoute('nouveau_cour');
        
    }
    
    /**
     * fns(filiere,niveau,semestre) 
     * on enregistre l'ID de la filiere,le niveau,le semestre dans la session
     * pour enregistrer des notes(route=nouvelle_note)
     * @Route("porte_nouvelle_note_traitement", name="porte_nouvelle_note_traitement")
     */
    public function porte_nouvelle_note_traitement(Request $request, SessionInterface $session, Application $application)
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
        
        return $this->redirectToRoute('nouvelle_note');
        
    }

    /**
     * on enregistre l'ID de l'etudiant dans la session
     * pour afficher ses notes(route=liste_notes)
     *  @Route("porte_releve_de_notes_traitement", name="porte_releve_de_notes_traitement")
     */
    public function porte_releve_de_notes_traitement(SessionInterface $session,Application $application)
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

}
