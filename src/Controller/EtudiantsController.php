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
use PDO;

/**
 * @Route("etudiants_", name="etudiants_")
 */
class EtudiantsController extends AbstractController
{
    /**
     * @Route("form_ajout", name="form_ajout")
     */
    public function form_ajout(Application $application): Response
    {
        return $this->render('etudiants/ajout.html.twig', [
            'filieres'=>$application->repo_filiere->findAll(),
            'classes'=>$application->repo_niveau->findAll()
        ]);
    }

    /**
     * @Route("ajout", name="ajout")
     */
    public function ajout(Request $request, Application $application)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) { return $this->redirectToRoute('app_login');}
        //sinon on insert les données
        $nom=$request->request->get('nom');
        $prenom=$request->request->get('prenom');
        $sexe=$request->request->get('sexe');
        $filiere=$application->repo_filiere->find($request->request->get('filiere'));
        $niveau=$application->repo_niveau->find($request->request->get('niveau'));
        if( !empty($nom) && !empty($prenom) && !empty($sexe) && !empty($filiere) && !empty($niveau)) {
            
            $data=array(
                'user'=>$application->repo_user->find($user),
                'nom'=>$nom,
                'prenom'=>$prenom,
                'sexe'=>$sexe,
                'filiere'=>$filiere,
                'niveau'=>$niveau
            );
            //on enregistre l'etudiant
            try {
                $application->new_etudiant($data);
                $this->addFlash('success', 'Enregistrement éffectué!');
                
            } catch (\Throwable $th) {
                //die('une erreur est survenue '.$th->getMessage());
                $this->addFlash('error', 'une erreur est survenue: '.$th->getMessage());
            }

        }
       
        return $this->redirectToRoute('etudiants_form_ajout');
    }

    /**
     * @Route("liste", name="liste")
     */
    public function liste(Application $application)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('etudiants/etudiants.html.twig', [
            'etudiants' => $application->repo_etudiant->etudiantsListe($user),
        ]);
    }

    /**
     * On supprime un etudiant par son id
     * @Route("suppression/{id}", name="suppression")
     */
    public function suppression(Application $application,$id)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on supprime les données
        $application->db->remove($id);
        $application->db->flush();

        return $this->redirectToRoute('etudiants_liste');
    }

    /**
     * @Route("choixFiliereNiveauxI", name="choixFiliereNiveauxI")
     */
    public function choixFiliereNiveauxI(Request $request, SessionInterface $session, Application $application)
    {

        if (!empty($request->request->get('filiere')) && !empty($request->request->get('classe'))) {
            $filiere = $application->repo_filiere->find($request->request->get("filiere"));
            $classe = $application->repo_niveau->find($request->request->get('classe'));
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
    public function i(Request $request, SessionInterface $session, Application $application): Response
    {
        $user=$this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);

        if (isset($_POST['enregistrer'])) {

            $check_array = $request->request->get("etudiantId");
            foreach ($request->request->get("etudiantName") as $key => $value) {
                if (in_array($request->request->get("etudiantName")[$key], $check_array)) {
                    //dd($request->request->get("inscription")[$key]);
                    // echo $request->request->get("matiereName")[$key];
                    // echo '<br>';
                    $etudiant = $application->repo_etudiant->find($request->request->get("etudiantName")[$key]);
                    $filiere = $application->repo_filiere->find($sessionF);
                    $classe = $application->repo_niveau->find($sessionN);

                    $data=array([
                        'user'=>$user,
                        'etudiant'=>$request->request->get("etudiantName")[$key],
                        'niveau'=>$sessionN,
                        'filiere'=>$sessionF,
                    ]);
                    
                    $application->affecter_etudiant($data);
                }
            }
        }

        return $this->render('etudiants/inscription.html.twig', [
            'mr' =>  $application->repo_etudiant->etudiantsPasInscris($user),
            'filieres' =>$application->repo_filiere->findBy([
                'user'=>$user]),
            'classes'  =>$application->repo_niveau->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("menuImprimer", name="menuImprimer")
     */
    public function menuImprimer(Request $request,SessionInterface $session,Application $application): Response
    {
        if (!empty($request->request->get('filiere')) && !empty($request->request->get('classe'))) {
            $filiere=$request->request->get('filiere');
            $classe=$request->request->get('classe');
            $repoFiliere=$application->repo_filiere->find($filiere);
            $repoClasse=$application->repo_niveau->find($classe);
            // $nomFiliere=$repoFiliere->getNom();
            // $nomClasse=$repoClasse->getNom();

            $session->set('filiere',$repoFiliere);
            $session->set('niveau',$repoClasse);
            //dd($session->get('filiere',[]),$session->get('niveau',[]));

            return $this->redirectToRoute('etudiants_imprimer');
          
        }
        
        return $this->render('etudiants/index.html.twig', [
            'filieres'=>$application->repo_filiere->findAll(),
            'classes'=>$application->repo_niveau->findAll(),

        ]);
    }

    /**
     * @Route("imprimer", name="imprimer")
     */
    public function imprimer(SessionInterface $session,Application $application): Response
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
            'etudiants'=>$application->repo_inscription->findBy([
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
