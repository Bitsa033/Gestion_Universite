<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Repository\NiveauRepository;
use App\Repository\FiliereRepository;
use App\Repository\EtudiantRepository;
use App\Repository\InscriptionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Enregistrement\EcritureEtudiant;
use Enregistrement\EcritureInscription;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * @Route("etudiants_", name="etudiants_")
 */
class EtudiantsController extends AbstractController
{

    /**
     * @Route("ajout", name="ajout")
     */
    public function ajout(Request $request, ManagerRegistry $end_e)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on insert les données
        if( !empty($request->request->get('nom')) && !empty($request->request->get('prenom')) && !empty($request->request->get('sexe'))) {

            $nom=$request->request->get('nom');
            $prenom=$request->request->get('prenom');
            $sexe=$request->request->get('sexe');
            $data=array(
                'nom'=>$nom,
                'prenom'=>$prenom,
                'sexe'=>$sexe
            );
            //on enregistre l'etudiant
            $enregistrerEtudiant = new EcritureEtudiant;
            $enregistrerEtudiant->Enregistrer($data,$user,$end_e);

            $this->addFlash('success', 'Enregistrement éffectué!');
        }
       
        return $this->render('etudiants/ajout.html.twig', [
            
        ]);
    }

    /**
     * @Route("liste", name="liste")
     */
    public function liste(EtudiantRepository $etudiantRepository)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('etudiants/etudiants.html.twig', [
            'etudiants' => $etudiantRepository->etudiantsListe($user),
        ]);
    }

    /**
     * On supprime un etudiant par son id
     * @Route("suppression/{id}", name="suppression")
     */
    public function suppression(Etudiant $etudiant, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on supprime les données
        $manager = $end->getManager();
        $manager->remove($etudiant);
        $manager->flush();

        return $this->redirectToRoute('etudiants_liste');
    }

    /**
     * @Route("choixFiliereNiveauxI", name="choixFiliereNiveauxI")
     */
    public function choixFiliereNiveauxI(Request $request, SessionInterface $session, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository)
    {

        if (!empty($request->request->get('filiere')) && !empty($request->request->get('classe'))) {
            $filiere = $filiereRepository->find($request->request->get("filiere"));
            $classe = $niveauRepository->find($request->request->get('classe'));
            $get_filiere = $session->get('filiere', []);
            $get_classe = $session->get('niveau', []);
            if (!empty($get_filiere)) {
                $session->set('filiere', $filiere);
                $session->set('niveau', $classe);
            }
            $session->set('filiere', $filiere);
            $session->set('niveau', $classe);
            //dd($session);

            //return $this->redirectToRoute('etudiants_i');
        }

        return $this->redirectToRoute('etudiants_i');
    }

    /**
     * @Route("i", name="i")
     */
    public function i(ManagerRegistry $managerRegistry, Request $request, SessionInterface $session, EtudiantRepository $etudiantRepository, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository): Response
    {
        $user=$this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $user = $this->getUser();

        if (isset($_POST['enregistrer'])) {

            $check_array = $request->request->get("etudiantId");
            foreach ($request->request->get("etudiantName") as $key => $value) {
                if (in_array($request->request->get("etudiantName")[$key], $check_array)) {
                    //dd($request->request->get("inscription")[$key]);
                    // echo $request->request->get("matiereName")[$key];
                    // echo '<br>';
                    $etudiant = $etudiantRepository->find($request->request->get("etudiantName")[$key]);
                    $filiere = $filiereRepository->find($sessionF);
                    $classe = $niveauRepository->find($sessionN);
                    
                    $inscrireEtudiant= new EcritureInscription;
                    $inscrireEtudiant->Enregistrer($etudiant,$classe,$filiere,$user,$managerRegistry);
                }
            }
        }

        return $this->render('etudiants/inscription.html.twig', [
            'mr' =>  $etudiantRepository->etudiantsUserPasInscris($user),
            'filieres' =>$filiereRepository->findBy([
                'user'=>$user]),
            'classes'  =>$niveauRepository->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("menuImprimer", name="menuImprimer")
     */
    public function menuImprimer(Request $request,SessionInterface $session,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository): Response
    {
        if (!empty($request->request->get('filiere')) && !empty($request->request->get('classe'))) {
            $filiere=$request->request->get('filiere');
            $classe=$request->request->get('classe');
            $repoFiliere=$filiereRepository->find($filiere);
            $repoClasse=$niveauRepository->find($classe);
            // $nomFiliere=$repoFiliere->getNom();
            // $nomClasse=$repoClasse->getNom();

            $session->set('filiere',$repoFiliere);
            $session->set('niveau',$repoClasse);
            //dd($session->get('filiere',[]),$session->get('niveau',[]));

            return $this->redirectToRoute('etudiants_imprimer');
          
        }
        
        return $this->render('etudiants/index.html.twig', [
            'filieres'=>$filiereRepository->findAll(),
            'classes'=>$niveauRepository->findAll(),

        ]);
    }

    /**
     * @Route("imprimer", name="imprimer")
     */
    public function imprimer(SessionInterface $session,InscriptionRepository $inscriptionRepository): Response
    {
        $filiere=$session->get('filiere',[]);
        $classe=$session->get('niveau',[]);

        $nomFiliere=$filiere->getSigle();
        $nomClasse=$classe->getNom();
        $idClasse=$classe->getId();

        //dd($nomFiliere);

        $pdfOptions= new Options();
        $pdfOptions->set('defaultFont','Arial');

        $dompdf=new Dompdf($pdfOptions);

        $html=$this->renderView('etudiants/imprimer.html.twig',[
            'etudiants'=>$inscriptionRepository->findBy([
                'filiere'=>$filiere,
                'niveau'=>$classe, 
            ]),
            'titre'=>"Liste des etudiants",
            'filiere'=>$nomFiliere,
            'classe'=>$nomClasse
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4','portrait');
        $dompdf->render();

        $output=$dompdf->output();
        $publicDirectory=$this->getParameter('images_directory') ;
        $pdfFilePath=$publicDirectory.'/'.$nomFiliere.'-'.$idClasse.'-Etudiants.pdf';

        file_put_contents($pdfFilePath,$output);

        $this->addFlash('success',"Le fichier pdf a été téléchargé");
        return $this->redirectToRoute('etudiants_menuImprimer');

        // return $this->render('etudiants/imprimer.html.twig', [
        //     'etudiants'=>$inscriptionRepository->findBy([
        //         'filiere'=>$filiere,
        //         'niveau'=>$classe, 
        //     ]),
        //     'titre'=>"Liste des etudiants",
        //     'filiere'=>$nomFiliere,
        //     'classe'=>$nomClasse

        // ]);
    }

    
}
