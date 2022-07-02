<?php

namespace App\Controller;

use App\Entity\Filiere;
use App\Repository\FiliereRepository;
use Doctrine\Persistence\ManagerRegistry;
use Enregistrement\EcritureFiliere;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
        return $this->redirectToRoute('filieres_index');
    }

    /**
     * @Route("index", name="index")
     */
    public function filiere(SessionInterface $session, FiliereRepository $filiereRepository, ManagerRegistry $end)
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

        return $this->render('filieres/filieres.html.twig', [
            'nb_rows' => $nb_row,
            'filieres' => $filiereRepository->findBy([
                'user'=>$user]),
        ]);
    }

    /**
     * @Route("suppression/{id}", name="suppression")
     */
    public function suppression_filiere(Filiere $filiere, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        //sinon on supprime les données
        $manager = $end->getManager();
        $manager->remove($filiere);
        $manager->flush();

        return $this->redirectToRoute('filieres_ajoutEt_liste');
    }
}
