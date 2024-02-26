<?php

namespace App\Controller;

use App\Application\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Dompdf\Dompdf;
use Dompdf\Options;

class NiveauxController extends AbstractController
{
    /**
     * @Route("quantite_niveau", name="quantite_niveau")
     */
    public function nb(SessionInterface $session, Request $request)
    {
        if (!empty($request->request->get('nb_row'))) {
            $nb_of_row = $request->request->get('nb_row');
            $get_nb_row = $session->get('nb_row_niveau', []);
            if (!empty($get_nb_row)) {
                $session->set('nb_row_niveau', $nb_of_row);
            }
            $session->set('nb_row_niveau', $nb_of_row);
            //   dd($session);
        }
        return $this->redirectToRoute('nouveau_niveau');
    }

    /**
     * @Route("liste_niveau", name="liste_niveau")
     */
    public function liste_niveau( Application $application)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        //on compte le nbre de classes presentes dans la base de donnees
        $nbniveaux = $application->repo_niveau->count([
            'user' => $user
        ]);

        return $this->render('niveaux/liste.html.twig', [
            'niveaux' => $application->repo_niveau->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("nouveau_niveau", name="nouveau_niveau")
     */
    public function nouveau_niveau(SessionInterface $session, Application $application, Request $request)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        //on compte le nbre de classes presentes dans la base de donnees
        $nbniveaux = $application->repo_niveau->count([
            'user' => $user
        ]);

        //si le nombre de classes est == 0 donc vide, on enregistre 6 classes
        if (empty($nbniveaux)) {

            for ($i = 1; $i < 7; $i++) {
                $niveau = new $application->table_niveau;
                $niveau->setUser($user);
                if ($i == 1) {
                    $niveau->setNom(ucfirst("Niveau 1 - Bts"));
                } elseif ($i == 2) {
                    $niveau->setNom(ucfirst("Niveau 2 - Bts"));
                } elseif ($i == 3) {
                    $niveau->setNom(ucfirst("Niveau 3 - Licence"));
                } elseif ($i == 4) {
                    $niveau->setNom(ucfirst("Niveau 4 - Master 1"));
                } elseif ($i == 5) {
                    $niveau->setNom(ucfirst("Niveau 5 - Master 2"));
                } elseif ($i == 6) {
                    $niveau->setNom(ucfirst("Niveau 6 - Master 3"));
                }

                $niveau->setCreatedAt(new \DateTime());
                $application->db->persist($niveau);
                $application->db->flush();
            }

            return $this->redirectToRoute('nouveau_niveau');
        }

        if (!empty($session->get('nb_row_niveau', []))) {
            $sessionLigne = $session->get('nb_row_niveau', []);
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
                    'nom' => $_POST['classe' . $i]
                );

                $application->nouveau_niveau($data,$user);
            }

            $this->addFlash('success', 'Enregistrement éffectué!');
        }

        return $this->render('niveaux/nouveau.html.twig', [
            'nb_rows' => $nb_row,
        ]);
    }

    /**
     * @Route("supprimer_niveau_/{id}", name="supprimer_niveau")
     */
    public function supprimer_niveau(Application $application,$id)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on supprime les données
        $application->db->remove($id);
        $application->db->flush();

        $this->addFlash('success', 'Donnée supprimée!');
        return $this->redirectToRoute('nouveau_niveau');
    }

    /**
     * @Route("imprimer_niveau", name="imprimer_niveau")
     */
    public function imprimer_niveau(Application $application)
    {
        $pdfOptions= new Options();
        $pdfOptions->set('defaultFont','Arial');

        $dompdf=new Dompdf($pdfOptions);

        $html=$this->renderView('niveaux/imprimer.html.twig',[
            'titre'=>'Liste des Niveaux',
            'classes'=>$application->repo_niveau->findAll()
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4','portrait');
        $dompdf->render();

        $output=$dompdf->output();
        $publicDirectory=$this->getParameter('documents') ;
        $pdfFilePath=$publicDirectory.'/Niveaux.pdf';

        file_put_contents($pdfFilePath,$output);

        $this->addFlash('success',"Le fichier pdf a été téléchargé");
        return $this->redirectToRoute('nouveau_niveau');
    }

}
