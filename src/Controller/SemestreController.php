<?php

namespace App\Controller;

use App\Application\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

class SemestreController extends AbstractController
{
    /**
     * @Route("quantite_semestre", name="quantite_semestre")
     */
    public function quantite_semestre(SessionInterface $session, Request $request)
    {
        if (!empty($request->request->get('nb_row'))) {
            $nb_of_row = $request->request->get('nb_row');
            $get_nb_row = $session->get('nb_row_semestre', []);
            if (!empty($get_nb_row)) {
                $session->set('nb_row_semestre', $nb_of_row);
            }
            $session->set('nb_row_semestre', $nb_of_row);
            //   dd($session);
        }
        return $this->redirectToRoute('nouveau_semestre');
    }
   
    /**
     * @Route("liste_semestre", name="liste_semestre", methods={"GET","POST"})
     */
    public function liste_semestre(Application $application): Response
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        //on compte le nbre de smestres presents dans la base de donnees
        $nbsemestre = $application->repo_semestre->count([
            
        ]);
        
        return $this->render('semestre/liste.html.twig', [
            'semestres' => $application->repo_semestre->findAll(),
        ]);
    }

    /**
     * @Route("nouveau_semestre", name="nouveau_semestre", methods={"GET","POST"})
     */
    public function nouveau_semestre(SessionInterface $session,Application $application): Response
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        //on compte le nbre de smestres presents dans la base de donnees
        $nbsemestre = $application->repo_semestre->count([
            
        ]);

        //si le nombre de semestre est == 0 donc vide, on enregistre 3 semestres
        if (empty($nbsemestre)) {

            for ($i = 1; $i < 4; $i++) {
                $semestre = new $application->table_semestre;
                $semestre->setUser($user);
                $semestre->setNom($i);
                $semestre->setCreatedAt(new \DateTime());
                $application->db->persist($semestre);
                $application->db->flush();
            }

        }

        if (!empty($session->get('nb_row_semestre', []))) {
            $sessionLigne = $session->get('nb_row_semestre', []);
        }
        else{
            $sessionLigne = 1;
        }
        $sessionNb = $sessionLigne;
        //on cree un tableau de valeurs
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
                    'nom' => $_POST['semestre'.$i]
                );

                $application->nouveau_semestre($data,$user);
            }
            
            $this->addFlash('success', 'Enregistrement éffectué!');

        }
        return $this->render('semestre/nouveau.html.twig', [
            'nb_rows' => $nb_row,
        ]);
    }

    /**
     * @Route("supprimer_semestre_{id}", name="supprimer_semestre", methods={"POST"})
     */
    public function supprimer_semestre(Request $request, Application $application,$id): Response
    {
        if ($this->isCsrfTokenValid('delete'.$id->getId(), $request->request->get('_token'))) {
            $application->db->remove($id);
            $application->db->flush();
        }

        return $this->redirectToRoute('liste_semestre', [], Response::HTTP_SEE_OTHER);
    }

    

    /**
     * @Route("imprimer_semestre", name="imprimer_semestre")
     */
    public function imprimer_semestre(Application $application)
    {
        $pdfOptions= new Options();
        $pdfOptions->set('defaultFont','Arial');

        $dompdf=new Dompdf($pdfOptions);

        $html=$this->renderView('semestre/imprimer.html.twig',[
            'titre'=>'Liste des semestres',
            'semestres'=>$application->repo_semestre->findAll()
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4','portrait');
        $dompdf->render();

        $output=$dompdf->output();
        $publicDirectory=$this->getParameter('documents') ;
        $pdfFilePath=$publicDirectory.'/Semestres.pdf';

        file_put_contents($pdfFilePath,$output);

        $this->addFlash('success',"Le fichier pdf a été téléchargé");
        return $this->redirectToRoute('nouveau_semestre');
    }
}
