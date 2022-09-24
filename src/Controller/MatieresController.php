<?php

namespace App\Controller;

use App\Entity\Ue;
use App\Entity\Matiere;
use App\Repository\UeRepository;
use App\Repository\NiveauRepository;
use App\Repository\FiliereRepository;
use App\Repository\MatiereRepository;
use App\Repository\SemestreRepository;
use Doctrine\Persistence\ManagerRegistry;
use Enregistrement\EcritureCours;
use Enregistrement\EcritureMatiere;
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
    public function index (MatiereRepository $matiereRepository)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        return $this->render('matieres/index.html.twig', [
            'matieres'=>$matiereRepository->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("add", name="add")
     */
    public function ajoutMatiere (SessionInterface $session,FiliereRepository $filiereRepository
    ,NiveauRepository $niveauRepository ,ManagerRegistry $end)
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
                    'filiere' => $filiereRepository->find($_POST['filiere']),
                    'niveau' =>$niveauRepository->find($_POST['niveau'])
                );

                $enregistrerMatiere= new EcritureMatiere;
                $enregistrerMatiere->Enregistrer($data,$user,$end);
            }

            $this->addFlash('success', 'Enregistrement éffectué!');
        } 

        return $this->render('matieres/add.html.twig', [
            'nb_rows' => $nb_row,
            'filieres'=>$filiereRepository->findAll(),
            'niveaux'=>$niveauRepository->findAll()
        ]);
    }

    /**
     * @Route("suppression_{id}", name="suppression")
     */
    public function suppression (Matiere $matiere, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        //sinon on supprime les données
        $manager = $end->getManager();
        $manager->remove($matiere);
        $manager->flush();

        return $this->redirectToRoute('matieres_ajoutEt_liste');
        
    }

    /**
     * @Route("imprimer", name="imprimer")
     */
    public function imprimer(MatiereRepository $matiereRepository)
    {
        $pdfOptions= new Options();
        $pdfOptions->set('defaultFont','Arial');

        $dompdf=new Dompdf($pdfOptions);

        $html=$this->renderView('matieres/imprimer.html.twig',[
            'titre'=>'Liste des matières',
            'matieres'=>$matiereRepository->findAll()
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
    function passerelleCours(SessionInterface $session,Request $request,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, SemestreRepository $semestreRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        if (!empty($request->request->get('filiere')) && !empty($request->request->get('niveau')) && !empty($request->request->get('semestre'))) {
            $filiere=$filiereRepository->find($request->request->get("filiere"));
            $semestre=$semestreRepository->find($request->request->get('semestre'));
            $niveau=$niveauRepository->find($request->request->get('niveau'));
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
            'filieres'=>$filiereRepository->findBy([
                'user'=>$user]),
            'semestres'=>$semestreRepository->findAll(),
            'niveaux'=>$niveauRepository->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("choixFiliereNiveauxSemestreC", name="choixFiliereNiveauxSemestreC")
     */
    public function choixFiliereNiveauxSemestreC(Request $request, SessionInterface $session, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,SemestreRepository $semestreRepository)
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

        return $this->redirectToRoute('matieres_t');
    }

    /**
     * @Route("t", name="t")
     */
    public function enregistrerCours(ManagerRegistry $managerRegistry, Request $request, SessionInterface $session,FiliereRepository $filiereRepository,NiveauRepository $niveauRepository, MatiereRepository $matiereRepository,SemestreRepository $semestreRepository): Response
    {
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        $user = $this->getUser();
        if (!empty($sessionF)) {
            $filiere = $filiereRepository->find($sessionF);
            
        }
        else {
            $filiere=null;
        }
        //classe
        if (!empty($sessionN)) {
            $classe = $niveauRepository->find($sessionN);
            
        }
        else {
            $classe=null;
        }
        //semestre
        if (!empty($sessionSe)) {
            $semestre = $semestreRepository->find($sessionSe);
            
        }
        else {
            $semestre=null;
        }
        if (isset($_POST['enregistrer']) && !empty($sessionF) && !empty($sessionSe) && !empty($sessionN)) {
            
            $check_array = $request->request->get("matiereId");
            foreach ($request->request->get("matiereName") as $key => $value) {
                if (in_array($request->request->get("matiereName")[$key], $check_array)) {
                    
                    $matiere = $matiereRepository->find($request->request->get("matiereName")[$key]);
                    $semestre = $semestreRepository->find($sessionSe);
                    $filiere = $filiereRepository->find($sessionF);
                    $classe = $niveauRepository->find($sessionN);

                    $enregistrerCours=new EcritureCours;
                    $enregistrerCours->Enregistrer($matiere,$classe,$filiere,$semestre,$user,$managerRegistry);
                    
                }
            }

            $this->addFlash('success', 'Enregistrement éffectué!');
        }

        return $this->render('matieres/cours.html.twig', [
            'mr' =>  $matiereRepository->matierePasEncoreUe($user,$filiere,$classe,$semestre),
            'filieres'=>$filiereRepository->findBy([
                'user'=>$user]),
            'classes' =>$niveauRepository->findBy([
                'user'=>$user]),
            'semestres' =>$semestreRepository->findAll()
        ]);
    }

    /**
     * @Route("liste_Ues", name="liste_Ues")
     */
    public function liste_Ues (SessionInterface $session,FiliereRepository $filiereRepository,NiveauRepository $niveauRepository,SemestreRepository $semestreRepository,UeRepository $ueRepository)
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
            'listeCours'=>$ueRepository->findBy([
                'user'=>$user,
                'niveau'=>$sessionN,
                'filiere'=>$sessionF,
                'semestre'=>$sessionSe

            ]),
            'filiere'=>$filiereRepository->find($sessionF),
            'classe'=>$niveauRepository->find($sessionN),
            'semestre'=>$semestreRepository->find($sessionSe),
            
        ]);
    }

    /**
     * @Route("ue_suppression/{id}", name="ue_suppression")
     */
    public function suppression_ue(Ue $ue, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        //sinon on supprime les données
        $manager = $end->getManager();
        $manager->remove($ue);
        $manager->flush();

        return $this->redirectToRoute('matieres_liste_Ues');
        
    }
    
}
