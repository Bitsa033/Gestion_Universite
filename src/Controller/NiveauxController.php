<?php

namespace App\Controller;

use App\Entity\Niveau;
use App\Repository\NiveauRepository;
use Doctrine\Persistence\ManagerRegistry;
use Enregistrement\EcritureClasse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * @Route("niveaux_", name="niveaux_")
 */
class NiveauxController extends AbstractController
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
        return $this->redirectToRoute('niveaux_add');
    }

    /**
     * @Route("add", name="add")
     */
    public function classe(SessionInterface $session, NiveauRepository $niveauRepository, Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        //on compte le nbre de classes presentes dans la base de donnees
        $nbniveaux = $niveauRepository->count([
            'user' => $user
        ]);

        //si le nombre de classes est == 0 donc vide, on enregistre 6 classes
        if (empty($nbniveaux)) {

            for ($i = 1; $i < 7; $i++) {
                $niveau = new Niveau();
                $niveau->setUser($user);
                if ($i == 1) {
                    $niveau->setNom(ucfirst("Niveau 1 | Bts"));
                } elseif ($i == 2) {
                    $niveau->setNom(ucfirst("Niveau 2 | Bts"));
                } elseif ($i == 3) {
                    $niveau->setNom(ucfirst("Niveau 3 | Licence"));
                } elseif ($i == 4) {
                    $niveau->setNom(ucfirst("Niveau 4 | Master"));
                } elseif ($i == 5) {
                    $niveau->setNom(ucfirst("Niveau 5 | Master"));
                } elseif ($i == 6) {
                    $niveau->setNom(ucfirst("Niveau 6 | Master"));
                }

                $niveau->setCreatedAt(new \DateTime());
                $manager = $end->getManager();
                $manager->persist($niveau);
                $manager->flush();
            }

            return $this->redirectToRoute('niveaux_add');
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
                    'nom' => $_POST['classe' . $i]
                );

                $ecritureClasse=new EcritureClasse;
                $ecritureClasse->Enregistrer($data,$user,$end);
            }

            $this->addFlash('success', 'Enregistrement éffectué!');
        }

        return $this->render('niveaux/add.html.twig', [
            'nb_rows' => $nb_row,
            'niveaux' => $niveauRepository->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("suppression/{id}", name="suppression")
     */
    public function suppression(Niveau $niveau, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on supprime les données
        $manager = $end->getManager();
        $manager->remove($niveau);
        $manager->flush();

        return $this->redirectToRoute('niveaux_add');
    }

    /**
     * @Route("imprimer", name="imprimer")
     */
    public function imprimer(NiveauRepository $niveauRepository)
    {
        $pdfOptions= new Options();
        $pdfOptions->set('defaultFont','Arial');

        $dompdf=new Dompdf($pdfOptions);

        $html=$this->renderView('niveaux/imprimer.html.twig',[
            'titre'=>'Liste des classes',
            'classes'=>$niveauRepository->findAll()
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4','portrait');
        $dompdf->render();

        $output=$dompdf->output();
        $publicDirectory=$this->getParameter('images_directory') ;
        $pdfFilePath=$publicDirectory.'/classes.pdf';

        file_put_contents($pdfFilePath,$output);

        $this->addFlash('success',"Le fichier pdf a été téléchargé");
        return $this->redirectToRoute('niveaux_add');
    }

}
