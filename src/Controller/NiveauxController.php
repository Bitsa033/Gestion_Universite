<?php

namespace App\Controller;

use App\Entity\Niveau;
use App\Repository\NiveauRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
        return $this->redirectToRoute('niveaux_index');
    }

    /**
     * Insertion et affichage des classes
     * @Route("index", name="index")
     */
    public function classe(SessionInterface $session, NiveauRepository $niveauRepository, Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
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
                    $niveau->setNom(strtoupper("BTS 1"));
                } elseif ($i == 2) {
                    $niveau->setNom(strtoupper("BTS 2"));
                } elseif ($i == 3) {
                    $niveau->setNom(strtoupper("Licence"));
                } elseif ($i == 4) {
                    $niveau->setNom(strtoupper("Master 1"));
                } elseif ($i == 5) {
                    $niveau->setNom(strtoupper("Master 2"));
                } elseif ($i == 6) {
                    $niveau->setNom(strtoupper("Master 3"));
                }

                $niveau->setCreatedAt(new \DateTime());
                $manager = $end->getManager();
                $manager->persist($niveau);
                $manager->flush();
            }

            return $this->redirectToRoute('niveaux_index');
        }

        //on recupere la valeur du nb_row stocker dans la session
        $sessionNb = $session->get('nb_row', []);
        //on cree un tableau qui permettra de generer plusieurs champs dans le post
        //en fonction de la valeur de nb_row
        $nb_row = array(1);
        //pour chaque valeur du compteur i, on ajoutera un champs de plus en consirerant que 
        //nb_row par defaut=1
        for ($i = 0; $i < $sessionNb; $i++) {
            $nb_row[$i] = $i;
        }
        //on cree la methode qui permettra d'enregistrer les infos du post dans la bd
        function insert_into_db($data, ManagerRegistry $end,$user)
        {
            foreach ($data as $key => $value) {
                $k[] = $key;
                $v[] =  $value;
            }
            $k = implode(",", $k);
            $v = implode(",", $v);
            $niveau = new Niveau();
            // echo $v;
            $niveau->setUser($user);
            $niveau->setNom(strtoupper($v));
            $niveau->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($niveau);
            $manager->flush();
        }

        //si on clic sur le boutton enregistrer et que les champs du post ne sont pas vide
        if (isset($_POST['enregistrer'])) {
            $session_nb_row = $session->get('nb_row', []);
            //dd($session_nb_row);
            for ($i = 0; $i < $session_nb_row; $i++) {
                $data = array(
                    'classe' => $_POST['classe' . $i]
                );

                insert_into_db($data, $end,$user);
            }

            // return $this->redirectToRoute('niveaux_index');
        }

        return $this->render('niveaux/niveaux.html.twig', [
            'nb_rows' => $nb_row,
            'niveaux' => $niveauRepository->niveauxUser($user),
            'niveauxNb' => $niveauRepository->count([
                'user' => $user
            ]),
        ]);
    }

    /**
     * Suppression des niveaux
     * @Route("suppression/{id}", name="suppression")
     */
    public function suppression(Niveau $niveau, ManagerRegistry $end)
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
        $manager->remove($niveau);
        $manager->flush();

        return $this->redirectToRoute('niveaux_ajoutEt_liste');
    }

    /**
     * @Route("infos/{id}", name="infos")
     */
    public function infos(Niveau $niveau)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on consulte les données
        //affichage d'un niveau particulier
        return $this->render('niveaux/consultation_niveau.html.twig', [
            'controller_name' => 'UniversgController',
            'niveau' => $niveau
        ]);
    }
}
