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

class EtudiantsController extends AbstractController
{
    /**
     * @Route("nouvel_etudiant", name="nouvel_etudiant")
     */
    public function nouvel_etudiant(Application $application): Response
    {
        return $this->render('etudiants/nouveau.html.twig', [
            'filieres'=>$application->repo_filiere->findAll(),
            'classes'=>$application->repo_niveau->findAll()
        ]);
    }

    /**
     * @Route("nouvel_etudiant_traitement", name="nouvel_etudiant_traitement")
     */
    public function nouvel_etudiant_traitement(Request $request, Application $application)
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
                $application->nouvel_etudiant($data);
                $this->addFlash('success', 'Enregistrement éffectué!');
                
            } catch (\Throwable $th) {
                //die('une erreur est survenue '.$th->getMessage());
                $this->addFlash('error', 'une erreur est survenue: '.$th->getMessage());
            }

        }
       
        return $this->redirectToRoute('nouvel_etudiant');
    }

    /**
     * @Route("liste_etudiant", name="liste_etudiant")
     */
    public function liste_etudiant(Application $application)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('etudiants/liste.html.twig', [
            'etudiants' => $application->repo_etudiant->etudiantsListe($user),
        ]);
    }

    /**
     * On supprime un etudiant par son id
     * @Route("supprimer_etudiant_/{id}", name="supprimer_etudiant")
     */
    public function supprimer_etudiant(Application $application,$id)
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

        return $this->redirectToRoute('liste_etudiant');
    }

    /**
     * @Route("inscription_etudiant", name="inscription_etudiant")
     */
    public function inscription_etudiant(Request $request, SessionInterface $session, Application $application): Response
    {
        $user=$this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $session_filiere = $session->get('filiere', []);
        $session_niveau = $session->get('niveau', []);

        if (isset($_POST['enregistrer'])) {

            $check_array = $request->request->get("etudiantId");
            foreach ($request->request->get("etudiantName") as $key => $value) {
                if (in_array($request->request->get("etudiantName")[$key], $check_array)) {
                    //dd($request->request->get("inscription")[$key]);
                    // echo $request->request->get("matiereName")[$key];
                    // echo '<br>';
                    $data=array([
                        'user'=>$user,
                        'etudiant'=>$request->request->get("etudiantName")[$key],
                        'niveau'=>$session_niveau,
                        'filiere'=>$session_filiere,
                    ]);
                    
                    $application->nouvelle_inscription($data);
                }
            }
        }

        return $this->render('etudiants/inscription.html.twig', [
            'etudiants_pas_inscrits' =>  $application->repo_etudiant->etudiantsPasInscris($user),
            'filieres' =>$application->repo_filiere->findBy([
                'user'=>$user]),
            'niveaux'  =>$application->repo_niveau->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("porte_imprimer_etudiant", name="porte_imprimer_etudiant")
     */
    public function porte_imprimer_etudiant(Request $request,SessionInterface $session,Application $application): Response
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

            return $this->redirectToRoute('imprimer_etudiant');
          
        }
        
        return $this->render('etudiants/porte_imprimer_etudiant.html.twig', [
            'filieres'=>$application->repo_filiere->findAll(),
            'niveaux'=>$application->repo_niveau->findAll(),

        ]);
    }

    /**
     * @Route("imprimer_etudiant", name="imprimer_etudiant")
     */
    public function imprimer_etudiant(SessionInterface $session,Application $application): Response
    {
        $filiere=$session->get('filiere',[]);
        $niveau=$session->get('niveau',[]);

        $nomFiliere=$filiere->getSigle();
        $nomNiveau=$niveau->getNom();
        $idClasse=$niveau->getId();

        //dd($nomFiliere);

        $pdfOptions= new Options();
        $pdfOptions->set('defaultFont','Arial');

        $dompdf=new Dompdf($pdfOptions);

        $html=$this->renderView('etudiants/imprimer_etudiant.html.twig',[
            'etudiants'=>$application->repo_inscription->findBy([
                'filiere'=>$filiere,
                'niveau'=>$niveau, 
            ]),
            'titre'=>"Liste des etudiants",
            'filiere'=>$nomFiliere,
            'niveau'=>$nomNiveau
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4','portrait');
        $dompdf->render();

        $output=$dompdf->output();
        $publicDirectory=$this->getParameter('documents') ;
        $pdfFilePath=$publicDirectory.'/'.$nomFiliere.'-'.$idClasse.'-Etudiants.pdf';

        file_put_contents($pdfFilePath,$output);

        $this->addFlash('success',"Le fichier pdf a été téléchargé");
        return $this->redirectToRoute('porte_imprimer_etudiant');

    }

    
}
