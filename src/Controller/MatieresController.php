<?php

namespace App\Controller;

use App\Application\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Dompdf\Dompdf;
use Dompdf\Options;

class MatieresController extends AbstractController
{
    /**
     * @Route("quantite_matiere", name="quantite_matiere")
     */
    public function quantite_matiere(SessionInterface $session, Request $request)
    {
        if (!empty($request->request->get('nb_row'))) {
            $nb_of_row = $request->request->get('nb_row');
            $get_nb_row = $session->get('nb_row', []);
            if (!empty($get_nb_row)) {
                $session->set('nb_row', $nb_of_row);
            }
            $session->set('nb_row', $nb_of_row);
            //   dd($session);
        }
        return $this->redirectToRoute('nouvelle_matiere');
    }

     /**
     * @Route("liste_matiere", name="liste_matiere")
     */
    public function liste_matiere (Application $application)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        return $this->render('matieres/liste.html.twig', [
            'matieres'=>$application->repo_matiere->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("nouvelle_matiere", name="nouvelle_matiere")
     */
    public function nouvelle_matiere (SessionInterface $session,Application $application)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        if (!empty($session->get('nb_row', []))) {
            $sessionLigne = $session->get('nb_row', []);
        }
        else{
            $sessionLigne = 1;
        }
        //on cree un tableau
        $nb_row = array(1);
        if (!empty( $sessionLigne)) {
           
            for ($i = 0; $i < $sessionLigne; $i++) {
                $nb_row[$i] = $i;
            }
        }

        //si on clic sur le boutton enregistrer et que les champs du post ne sont pas vide
        if (isset($_POST['enregistrer'])) {
            
            for ($i = 0; $i < $sessionLigne; $i++) {
                $data = array(
                    'nom' => $_POST['matiere' . $i],
                    'filiere' => $_POST['filiere'],
                    'niveau' =>$_POST['niveau'],
                    'semestre'=>$_POST['semestre'],
                    'note'=>$_POST['note'  . $i],
                    'code'=>$application->repo_filiere->find($_POST['filiere'])->getNom()." " .random_int(120,300)
                );

                $application->nouvelle_matiere($data,$user);
            }

            $this->addFlash('success', 'Enregistrement éffectué!');
        } 

        return $this->render('matieres/nouvelle.html.twig', [
            'nb_rows' => $nb_row,
            'filieres'=>$application->repo_filiere->findAll(),
            'niveaux'=>$application->repo_niveau->findAll(),
            'semestres'=>$application->repo_semestre->findAll()
        ]);
    }

    /**
     * @Route("supprimer_matiere_/{id}", name="supprimer_matiere")
     */
    public function supprimer_matiere (Application $application, $id)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        //sinon on supprime les données
        $application->db->remove($id);
        $application->db->flush();

        return $this->redirectToRoute('liste_matiere');
        
    }

    /**
     * @Route("imprimer_matiere", name="imprimer_matiere")
     */
    public function imprimer_matiere(Application $application)
    {
        $pdfOptions= new Options();
        $pdfOptions->set('defaultFont','Arial');

        $dompdf=new Dompdf($pdfOptions);

        $html=$this->renderView('matieres/imprimer.html.twig',[
            'titre'=>'Liste des matières',
            'matieres'=>$application->repo_matiere->findAll()
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4','portrait');
        $dompdf->render();

        $output=$dompdf->output();
        $publicDirectory=$this->getParameter('documents') ;
        $pdfFilePath=$publicDirectory.'/Matieres.pdf';

        file_put_contents($pdfFilePath,$output);

        $this->addFlash('success',"Le fichier pdf a été téléchargé");
        return $this->redirectToRoute('nouvelle_matiere');
    }

    /**
     * nous avons d'abord un formulaire pour,
     * choisir la filiere,le niveau, et le,
     * semestre qu'on appelle[porte_nouveau_cour],
     * son traitement se trouve dans la route[porte_nouveau_cour_traitement]
     * @Route("nouveau_cour", name="nouveau_cour")
     */
    public function nouveau_cour(Request $request, SessionInterface $session,Application $application): Response
    {
        $user=$this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $filiere=$session->get('filiere');
        $niveau=$session->get('niveau');
        $semestre=$session->get('semestre');
        
        return $this->render('matieres/nouveau_cour.html.twig', [
            
            'filieres'=>$application->repo_filiere->findBy([
                'user'=>$user]),
            'niveaux' =>$application->repo_niveau->findBy([
                'user'=>$user]),
            'semestres' =>$application->repo_semestre->findAll(),
            'matieres' => $application->repo_matiere->matierePasEncoreUe($user,$filiere,$niveau,$semestre),
            
        ]);
    }

    /**
     * on traite le formulaire d'insertion des notes[route=nouveau_cour]
     * @Route("nouveau_cour_traitement", name="nouveau_cour_traitement")
     */
    public function nouveau_cour_traitement(Request $request, SessionInterface $session,Application $application)
    {
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $filiere = $session->get('filiere', []);
        $niveau = $session->get('niveau', []);
        $semestre = $session->get('semestre', []);
        $user = $this->getUser();
        
        if (isset($_POST['enregistrer']) && !empty($filiere) && !empty($niveau) && !empty($semestre)) {
            
            $check_array = $request->request->get("matiereId");
            foreach ($request->request->get("matiereName") as $key => $value) {
                if (in_array($request->request->get("matiereName")[$key], $check_array)) {
                    
                    $data=([
                        'user'=>$user,
                        'matiere'=>$request->request->get("matiereName")[$key],
                        'niveau'=>$niveau,
                        'filiere'=>$filiere,
                        'semestre'=>$semestre
                    ]);

                    $application->nouveau_cour($data);
                    
                }
            }

            $this->addFlash('success', 'Enregistrement éffectué!');
        }
        return $this->redirectToRoute('nouveau_cour');

        
    }

    /**
     * liste des cours par filiere,niveau et semestre
     * @Route("liste_cours", name="liste_cours")
     */
    public function liste_cours (SessionInterface $session,Application $application)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on consulte les données
        $sessionF=$session->get('filiere',[]);
        $sessionN=$session->get('niveau',[]);
        $sessionSe=$session->get('semestre',[]);
       
        return $this->render('matieres/liste_cours.html.twig', [
            'listeCours'=>$application->repo_ue->findBy([
                'user'=>$user,
                'niveau'=>$sessionN,
                'filiere'=>$sessionF,
                'semestre'=>$sessionSe

            ]),
            'filiere'=>$application->repo_filiere->find($sessionF),
            'classe'=>$application->repo_niveau->find($sessionN),
            'semestre'=>$application->repo_semestre->find($sessionSe),
            
        ]);
    }

    /**
     * @Route("supprimer_cour_/{id}", name="supprimer_cour")
     */
    public function supprimer_cour(Application $application,$id)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        //sinon on supprime les données
        $application->db->remove($id);
        $application->db->flush();

        return $this->redirectToRoute('liste_cours');
        
    }
    
}
