<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Application\Application;

class FilieresController extends AbstractController
{

    /**
     * @Route("quantite_filiere", name="quantite_filiere")
     */
    public function quantite_filiere(SessionInterface $session, Request $request)
    {
        if (!empty($request->request->get('nb_row'))) {
            $nb_of_row = $request->request->get('nb_row');
            $get_nb_row = $session->get('nb_row', []);
            if (!empty($get_nb_row)) {
                $session->set('nb_row', $nb_of_row);
            }
            $session->set('nb_row', $nb_of_row);
            //dd($session);
        }
        return $this->redirectToRoute('quantite_filiere');
    }

    /**
     * @Route("liste_filiere", name="liste_filiere")
     */
    public function liste_filiere(Application $application)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('filieres/liste.html.twig', [
            'filieres' => $application->repo_filiere->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("nouvelle_filiere", name="nouvelle_filiere")
     */
    public function nouvelle_filiere(SessionInterface $session,Application $application)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        // on cherche la session nb
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
                    'nom' => $_POST['filiere' . $i],
                    'sigle'    => $_POST['abbr' . $i]
                );

                $application->nouvelle_filiere($data,$user);

                
            }
            $this->addFlash('success', 'Enregistrement éffectué!');

        }

        return $this->render('filieres/nouvelle.html.twig', [
            'nb_rows' => $nb_row,
        ]);
    }

    /**
     * @Route("imprimer_filiere", name="imprimer_filiere")
     */
    public function imprimer_filiere(Application $application)
    {
        
        $pdfOptions= new Options();
        $pdfOptions->set('defaultFont','Arial');

        $dompdf=new Dompdf($pdfOptions);

        $html=$this->renderView('filieres/imprimer_filiere.html.twig',[
            'titre'=>'Liste des filières',
            'filieres'=>$application->repo_filiere->findAll()
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4','portrait');
        $dompdf->render();

        $output=$dompdf->output();
        $publicDirectory=$this->getParameter('documents') ;
        $pdfFilePath=$publicDirectory.'/Filieres.pdf';

        file_put_contents($pdfFilePath,$output);

        $this->addFlash('success',"Le fichier pdf a été téléchargé");
        return $this->redirectToRoute('nouvelle_filiere');

    }
    

}
