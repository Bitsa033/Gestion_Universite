<?php

namespace App\Controller;

use App\Repository\FiliereRepository;
use Doctrine\Persistence\ManagerRegistry;
use Enregistrement\EcritureFiliere;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("filieres_", name="filieres_")
 */
class FilieresController extends AbstractController
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
            //dd($session);
        }
        return $this->redirectToRoute('filieres_add');
    }

    /**
     * @Route("index", name="index")
     */
    public function index(FiliereRepository $filiereRepository)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('filieres/index.html.twig', [
            'filieres' => $filiereRepository->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("add", name="add")
     */
    public function add(SessionInterface $session, ManagerRegistry $end)
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
                    'nom' => $_POST['filiere' . $i],
                    'sigle'    => $_POST['abbr' . $i]
                );

                $ecritureFiliere=new EcritureFiliere;
                $ecritureFiliere->Enregistrer($data,$user,$end);
            }
            $this->addFlash('success', 'Enregistrement éffectué!');

        }

        return $this->render('filieres/add.html.twig', [
            'nb_rows' => $nb_row,
        ]);
    }

    /**
     * @Route("imprimer", name="imprimer")
     */
    public function imprimer(FiliereRepository $filiereRepository)
    {
        
        $pdfOptions= new Options();
        $pdfOptions->set('defaultFont','Arial');

        $dompdf=new Dompdf($pdfOptions);

        $html=$this->renderView('filieres/imprimer.html.twig',[
            'titre'=>'Liste des filières',
            'filieres'=>$filiereRepository->findAll()
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4','portrait');
        $dompdf->render();

        $output=$dompdf->output();
        $publicDirectory=$this->getParameter('images_directory') ;
        $pdfFilePath=$publicDirectory.'/filieres.pdf';

        file_put_contents($pdfFilePath,$output);

        $this->addFlash('success',"Le fichier pdf a été téléchargé");
        return $this->redirectToRoute('filieres_add');
        //return new Response('Ce pdf a été téléchargé ');

        // return $this->render('filieres/imprimer.html.twig',[
        //     'titre'=>'Liste des filières',
        //     'filieres'=>$filiereRepository->findAll()
        // ]);

    }
    

}
