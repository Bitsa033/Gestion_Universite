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

/**
 * @Route("matieres_",name="matieres_")
 */
class MatieresController extends AbstractController
{
    /**
     * @Route("nb", name="nb")
     */
    public function nb(SessionInterface $session, Request $request)
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
        return $this->redirectToRoute('matieres_add');
    }

     /**
     * @Route("index", name="index")
     */
    public function index (Application $application)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        return $this->render('matieres/index.html.twig', [
            'matieres'=>$application->repo_matiere->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("add", name="add")
     */
    public function ajoutMatiere (SessionInterface $session,Application $application)
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
        $sessionNb = $sessionLigne;
        //on cree un tableau
        $nb_row = array(1);
        if (!empty( $sessionNb)) {
           
            for ($i = 0; $i < $sessionNb; $i++) {
                $nb_row[$i] = $i;
            }
        }

        //si on clic sur le boutton enregistrer et que les champs du post ne sont pas vide
        if (isset($_POST['enregistrer'])) {
            
            for ($i = 0; $i < $sessionNb; $i++) {
                $data = array(
                    'nom' => $_POST['matiere' . $i],
                    'filiere' => $application->repo_filiere->find($_POST['filiere']),
                    'niveau' =>$application->repo_niveau->find($_POST['niveau']),
                    'semestre'=>$application->repo_semestre->find($_POST['semestre']),
                    'note'=>$_POST['note'  . $i],
                    'code'=>$application->repo_filiere->find($_POST['filiere'])->getNom()." " .random_int(120,300)
                );

                $application->new_matiere($data,$user);
            }

            $this->addFlash('success', 'Enregistrement éffectué!');
        } 

        return $this->render('matieres/add.html.twig', [
            'nb_rows' => $nb_row,
            'filieres'=>$application->repo_filiere->findAll(),
            'niveaux'=>$application->repo_niveau->findAll(),
            'semestres'=>$application->repo_semestre->findAll()
        ]);
    }

    /**
     * @Route("suppression_{id}", name="suppression")
     */
    public function suppression (Application $application, $id)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        //sinon on supprime les données
        $application->db->remove($id);
        $application->db->flush();

        return $this->redirectToRoute('matieres_ajoutEt_liste');
        
    }

    /**
     * @Route("imprimer", name="imprimer")
     */
    public function imprimer(Application $application)
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
        $publicDirectory=$this->getParameter('images_directory') ;
        $pdfFilePath=$publicDirectory.'/matieres.pdf';

        file_put_contents($pdfFilePath,$output);

        $this->addFlash('success',"Le fichier pdf a été téléchargé");
        return $this->redirectToRoute('matieres_add');
    }

    /**
     * @Route("passerelleCours", name="passerelleCours")
     */
    function passerelleCours(SessionInterface $session,Request $request,Application $application){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        if (!empty($request->request->get('filiere')) && !empty($request->request->get('niveau')) && !empty($request->request->get('semestre'))) {
            $filiere=$application->repo_filiere->find($request->request->get("filiere"));
            $semestre=$application->repo_semestre->find($request->request->get('semestre'));
            $niveau=$application->repo_niveau->find($request->request->get('niveau'));
            $get_filiere=$session->get('filiere',[]);
            $get_semestre=$session->get('semestre',[]);
            $get_niveau=$session->get('niveau',[]);
            if (!empty($get_filiere) && !empty($get_semestre) && !empty($get_niveau)) {
              $session->set('filiere',$filiere);
              $session->set('semestre',$semestre);
              $session->set('niveau',$niveau);
            }
            //dd($session);
            $session->set('filiere',$filiere);
            $session->set('semestre',$semestre);
            $session->set('niveau',$niveau);
            return $this->redirectToRoute('matieres_t');
        }
        return $this->render('matieres/passerelleCours.html.twig',[
            'filieres'=>$application->repo_filiere->findBy([
                'user'=>$user]),
            'semestres'=>$application->repo_semestre->findAll(),
            'niveaux'=>$application->repo_niveau->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("choixFiliereNiveauxSemestreC", name="choixFiliereNiveauxSemestreC")
     */
    public function choixFiliereNiveauxSemestreC(Request $request, SessionInterface $session, Application $application)
    {

        if (!empty($request->request->get('filiere')) && !empty($request->request->get('classe')) && !empty($request->request->get('semestre'))) {
            $filiere = $application->repo_filiere->find($request->request->get("filiere"));
            $niveau = $application->repo_niveau->find($request->request->get('classe'));
            $semestre=$application->repo_semestre->find($request->request->get('semestre'));
            $get_filiere = $session->get('filiere', []);
            $get_classe = $session->get('niveau', []);
            $get_semestre = $session->get('semestre', []);
            if (!empty($get_filiere) && !empty($get_classe) && !empty($get_semestre)) {
                $session->set('filiere', $filiere);
                $session->set('niveau', $niveau);
                $session->set('semestre', $semestre);
            }
            $session->set('filiere', $filiere);
            $session->set('niveau', $niveau);
            $session->set('semestre', $semestre);
            //dd($session);

            //return $this->redirectToRoute('etudiants_i');
        }

        return $this->redirectToRoute('matieres_t');
    }

    /**
     * @Route("t", name="t")
     */
    public function enregistrerCours(Request $request, SessionInterface $session,Application $application): Response
    {
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        $user = $this->getUser();
        if (!empty($sessionF)) {
            $filiere = $application->repo_filiere->find($sessionF);
            
        }
        else {
            $filiere=null;
        }
        //classe
        if (!empty($sessionN)) {
            $niveau = $application->repo_niveau->find($sessionN);
            
        }
        else {
            $niveau=null;
        }
        //semestre
        if (!empty($sessionSe)) {
            $semestre = $application->repo_semestre->find($sessionSe);
            
        }
        else {
            $semestre=null;
        }
        if (isset($_POST['enregistrer']) && !empty($sessionF) && !empty($sessionSe) && !empty($sessionN)) {
            
            $check_array = $request->request->get("matiereId");
            foreach ($request->request->get("matiereName") as $key => $value) {
                if (in_array($request->request->get("matiereName")[$key], $check_array)) {
                    
                    $matiere = $application->repo_matiere->find($request->request->get("matiereName")[$key]);
                    $semestre = $application->repo_semestre->find($sessionSe);
                    $filiere = $application->repo_filiere->find($sessionF);
                    $niveau = $application->repo_niveau->find($sessionN);

                    $data=([
                        'user'=>$application->repo_user->find($user),
                        'matiere'=>$matiere,
                        'niveau'=>$niveau,
                        'filiiere'=>$filiere,
                        'semestre'=>$semestre
                    ]);

                    $application->affecter_matiere($data);
                    
                }
            }

            $this->addFlash('success', 'Enregistrement éffectué!');
        }

        return $this->render('matieres/cours.html.twig', [
            'mr' =>  $application->repo_matiere->matierePasEncoreUe($user,$filiere,$niveau,$semestre),
            'filieres'=>$application->repo_filiere->findBy([
                'user'=>$user]),
            'classes' =>$application->repo_niveau->findBy([
                'user'=>$user]),
            'semestres' =>$application->repo_semestre->findAll()
        ]);
    }

    /**
     * @Route("liste_Ues", name="liste_Ues")
     */
    public function liste_Ues (SessionInterface $session,Application $application)
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
       
        return $this->render('matieres/listeC.html.twig', [
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
     * @Route("ue_suppression/{id}", name="ue_suppression")
     */
    public function suppression_ue(Application $application,$id)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        //sinon on supprime les données
        $application->db->remove($id);
        $application->db->flush();

        return $this->redirectToRoute('matieres_liste_Ues');
        
    }
    
}
