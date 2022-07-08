<?php

namespace App\Controller;

use App\Entity\Semestre;
use App\Repository\SemestreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Enregistrement\EcritureSemestre;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * @Route("semestres_", name="semestres_")
 */
class SemestreController extends AbstractController
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
        return $this->redirectToRoute('semestres_add');
    }
   
    /**
     * @Route("add", name="add", methods={"GET","POST"})
     */
    public function semestre(SessionInterface $session,SemestreRepository $semestreRepository, ManagerRegistry $end): Response
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        //on compte le nbre de smestres presents dans la base de donnees
        $nbsemestre = $semestreRepository->count([
            
        ]);

        //si le nombre de semestre est == 0 donc vide, on enregistre 3 semestres
        if (empty($nbsemestre)) {

            for ($i = 1; $i < 4; $i++) {
                $semestre = new Semestre();
                $semestre->setUser($user);
                $semestre->setNom($i);
                $semestre->setCreatedAt(new \DateTime());
                $manager = $end->getManager();
                $manager->persist($semestre);
                $manager->flush();
            }

        }

        if (!empty($session->get('nb_row', []))) {
            $sessionLigne = $session->get('nb_row', []);
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

                $ecritureSemestre=new EcritureSemestre;
                $ecritureSemestre->Enregistrer($data,$user,$end);
            }
            
            $this->addFlash('success', 'Enregistrement éffectué!');

        }
        return $this->render('semestre/add.html.twig', [
            'nb_rows' => $nb_row,
            'semestres' => $semestreRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"POST"})
     */
    public function delete(Request $request, Semestre $semestre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$semestre->getId(), $request->request->get('_token'))) {
            $entityManager->remove($semestre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('semestre_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("imprimer", name="imprimer")
     */
    public function imprimer(SemestreRepository $semestreRepository)
    {
        $pdfOptions= new Options();
        $pdfOptions->set('defaultFont','Arial');

        $dompdf=new Dompdf($pdfOptions);

        $html=$this->renderView('semestre/imprimer.html.twig',[
            'titre'=>'Liste des semestres',
            'semestres'=>$semestreRepository->findAll()
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4','portrait');
        $dompdf->render();

        $output=$dompdf->output();
        $publicDirectory=$this->getParameter('images_directory') ;
        $pdfFilePath=$publicDirectory.'/semestres.pdf';

        file_put_contents($pdfFilePath,$output);

        $this->addFlash('success',"Le fichier pdf a été téléchargé");
        return $this->redirectToRoute('semestres_add');
    }
}
