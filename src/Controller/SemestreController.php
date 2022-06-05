<?php

namespace App\Controller;

use App\Entity\Semestre;
use App\Form\SemestreType;
use App\Repository\SemestreRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

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
        return $this->redirectToRoute('semestres_index');
    }
   
    /**
     * @Route("index", name="index", methods={"GET","POST"})
     */
    public function index(SessionInterface $session,SemestreRepository $semestreRepository,Request $request, ManagerRegistry $end): Response
    {
        
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        //on compte le nbre de classes presentes dans la base de donnees
        $nbsemestre = $semestreRepository->count([
            
        ]);

        //si le nombre de classes est == 0 donc vide, on enregistre 6 classes
        if (empty($nbsemestre)) {

            for ($i = 1; $i < 3; $i++) {
                $semestre = new Semestre();
                $semestre->setUser($user);
                $semestre->setNom($i);
                
                $semestre->setCreatedAt(new \DateTime());
                $manager = $end->getManager();
                $manager->persist($semestre);
                $manager->flush();
            }

            return $this->redirectToRoute('semestres_index');
        }
        
        //on recupere la valeur du nb_row stocker dans la session
        
        $sessionNb = $session->get('nb_row', []);
        //on cree un tableau qui permettra de generer plusieurs champs dans le post
        //en fonction de la valeur de nb_row
        $nb_row = array(1);
        //pour chaque valeur du compteur i, on ajoutera un champs de plus en consirerant que 
        //nb_row par defaut=1
        if (!empty( $sessionNb)) {
           
            for ($i = 0; $i < $sessionNb; $i++) {
                $nb_row[$i] = $i;
            }
        }
        $session_nb_row=1;
        //on cree la methode qui permettra d'enregistrer les infos du post dans la bd
        function insert_into_db($data, ManagerRegistry $end,$user)
        {
            foreach ($data as $key => $value) {
                $k[] = $key;
                $v[] =  $value;
            }
            $k = implode(",", $k);
            $v = implode(",", $v);
            $semestre = new Semestre();
            // echo $v;
            $semestre->setUser($user);
            $semestre->setNom(strtoupper($v));
            $semestre->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($semestre);
            $manager->flush();
        }

        //si on clic sur le boutton enregistrer et que les champs du post ne sont pas vide
        if (isset($_POST['enregistrer'])) {
            $session_nb_row = $session->get('nb_row', []);
            //dd($session_nb_row);
            for ($i = 0; $i < $session_nb_row; $i++) {
                $data = array(
                    'semestre' => $_POST['semestre'.$i]
                );

                insert_into_db($data, $end,$user);
            }

        }
        return $this->render('semestre/index.html.twig', [
            'nb_rows' => $nb_row,
            'semestres' => $semestreRepository->findAll(),
        ]);
    }

    /**
     * @Route("show_{id}", name="show", methods={"GET"})
     */
    public function show(Semestre $semestre): Response
    {
        return $this->render('semestre/show.html.twig', [
            'semestre' => $semestre,
        ]);
    }

    /**
     * @Route("edit_{id}_edit", name="edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Semestre $semestre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SemestreType::class, $semestre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('semestre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('semestre/edit.html.twig', [
            'semestre' => $semestre,
            'form' => $form->createView(),
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
}
