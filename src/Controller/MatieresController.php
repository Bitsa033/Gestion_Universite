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
     * @Route("matieres_nb", name="matieres_nb")
     */
    public function matieres_nb(SessionInterface $session, Request $request)
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
        return $this->redirectToRoute('matieres_new_form');
    }

     /**
     * @Route("matieres_liste", name="matieres_liste")
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
     * @Route("matieres_new_form", name="matieres_new_form")
     */
    public function matieres_new_form (SessionInterface $session,Application $application)
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
     * @Route("matieres_delete/{id}", name="matieres_delete")
     */
    public function matieres_delete (Application $application, $id)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        //sinon on supprime les données
        $application->db->remove($id);
        $application->db->flush();

        return $this->redirectToRoute('matieres_liste');
        
    }

    /**
     * @Route("matieres_imprimer", name="matieres_imprimer")
     */
    public function matieres_imprimer(Application $application)
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
     * on crée des cours pour la filiere,le niveau et le semestre
     * ici,on choisi la matiere qui est deja dans la base données
     * @Route("matieres_transert_form", name="matieres_transert_form")
     */
    public function matieres_transert_form(Request $request, SessionInterface $session,Application $application): Response
    {
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        $user = $this->getUser();
        
        if (isset($_POST['enregistrer']) && !empty($sessionF) && !empty($sessionSe) && !empty($sessionN)) {
            
            $check_array = $request->request->get("matiereId");
            foreach ($request->request->get("matiereName") as $key => $value) {
                if (in_array($request->request->get("matiereName")[$key], $check_array)) {
                    
                    $data=([
                        'user'=>$user,
                        'matiere'=>$request->request->get("matiereName")[$key],
                        'niveau'=>$sessionN,
                        'filiere'=>$sessionF,
                        'semestre'=>$sessionSe
                    ]);

                    $application->affecter_matiere($data);
                    
                }
            }

            $this->addFlash('success', 'Enregistrement éffectué!');
        }

        return $this->render('matieres/cours.html.twig', [
            'matieres' =>  $application->repo_matiere->matierePasEncoreUe(
                $user,$sessionF,$sessionN,$sessionSe),
            'filieres'=>$application->repo_filiere->findBy([
                'user'=>$user]),
            'niveaux' =>$application->repo_niveau->findBy([
                'user'=>$user]),
            'semestres' =>$application->repo_semestre->findAll()
        ]);
    }

    /**
     * liste des matieres par filiere,niveau et semestre
     * @Route("cours_liste", name="cours_liste")
     */
    public function cours_liste (SessionInterface $session,Application $application)
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
     * @Route("cours_delete/{id}", name="ue_delete")
     */
    public function cours_delete(Application $application,$id)
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
