<?php

namespace App\Controller;

use App\Entity\Filiere;
use App\Entity\Ue;
use App\Entity\Matiere;
use App\Entity\Niveau;
use App\Repository\UeRepository;
use App\Repository\NiveauRepository;
use App\Repository\FiliereRepository;
use App\Repository\MatiereRepository;
use App\Repository\SemestreRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("matieres_",name="matieres_")
 */
class MatieresController extends AbstractController
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
        return $this->redirectToRoute('matieres_index');
    }

     /**
     * @Route("index", name="index")
     */
    public function ajout (SessionInterface $session,MatiereRepository $matiereRepository, Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
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
            $matiere = new Matiere();
            // echo $v;
            $matiere->setUser($user);
            $matiere->setNom(strtoupper($v));
            $matiere->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($matiere);
            $manager->flush();
        }

        //si on clic sur le boutton enregistrer et que les champs du post ne sont pas vide
        if (isset($_POST['enregistrer'])) {
            $session_nb_row = $session->get('nb_row', []);
            //dd($session_nb_row);
            for ($i = 0; $i < $session_nb_row; $i++) {
                $data = array(
                    'matiere' => $_POST['matiere' . $i]
                );

                insert_into_db($data, $end,$user);
            }
        } 

        return $this->render('matieres/matieres.html.twig', [
            'nb_rows' => $nb_row,
            'matieres'=>$matiereRepository->matieresUser($user),
            'matieresNb'=>$matiereRepository->count([
                'user'=>$user
            ]),
        ]);
    }

    /**
     * @Route("miseAjour/{id}", name="miseAjour")
     */
    public function miseAjour (Matiere $matiere, Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on modifie les données
        if (!empty($request->request->get('nom_mat'))) {
            $matiere->setNom($request->request->get('nom_mat'));
            $matiere->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->flush();

            return $this->redirectToRoute('matieres_ajoutEt_liste');
        } 

        $repos=$this->getDoctrine()->getRepository(Matiere::class);
        $matieres = $repos->findAll();
        return $this->render('matieres/matiere_miseAjour.html.twig', [
            'controller_name' => 'UniversgController',
            'matieres'=>$matieres,
            'matiere'=>$matiere
        ]);
    }

    /**
     * Suppression des matieres
     * @Route("suppression/{id}", name="suppression")
     */
    public function suppression (Matiere $matiere, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        //sinon on supprime les données
        $manager = $end->getManager();
        $manager->remove($matiere);
        $manager->flush();

        return $this->redirectToRoute('matieres_ajoutEt_liste');
        
    }

    /**
     * on choisi la filiere, le semestre et la classe  pour la creation des cours
     * @Route("choix_filiereEtNiveau_Ue", name="choix_filiereEtNiveau_Ue")
     */
    function choix_filiereEtNiveau_Ue(SessionInterface $session,Request $request,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, SemestreRepository $semestreRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        if (!empty($request->request->all())) {
            $filiere=$filiereRepository->find($request->request->get("filiere"));
            $semestre=$semestreRepository->find($request->request->get('semestre'));
            $niveau=$niveauRepository->find($request->request->get('niveau'));
            $get_filiere=$session->get('filiere',[]);
            $get_semestre=$session->get('semestre',[]);
            $get_niveau=$session->get('niveau',[]);
            if (!empty($get_filiere) && !empty($get_semestre) && !empty($get_niveau)) {
              $session->set('filiere',$filiere);
              $session->set('semestre',$semestre);
              $session->set('niveau',$niveau);
            }
            //dd($session);
            $session->set('filiere',$filiere);
            $session->set('semestre',$semestre);
            $session->set('niveau',$niveau);
            return $this->redirectToRoute('matieres_t');
        }
        return $this->render('matieres/filiereNiveau_Ue.html.twig',[
            'filieres'=>$filiereRepository->filieresUser($user),
            'semestres'=>$semestreRepository->findAll(),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * @Route("t", name="t")
     */
    public function t(ManagerRegistry $managerRegistry, Request $request, SessionInterface $session, MatiereRepository $matiereRepository,SemestreRepository $semestreRepository): Response
    {
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        $user = $this->getUser();

        if (isset($_POST['enregistrer'])) {

            $check_array = $request->request->get("matiereId");
            foreach ($request->request->get("matiereName") as $key => $value) {
                if (in_array($request->request->get("matiereName")[$key], $check_array)) {
                    //dd($request->request->get("inscription")[$key]);
                    // echo $request->request->get("matiereName")[$key];
                    // echo '<br>';
                    $matiere = $matiereRepository->find($request->request->get("matiereName")[$key]);
                    $semestre = $semestreRepository->find($sessionSe);
                    $filiere = $this->getDoctrine()->getRepository(Filiere::class)->find($sessionF);
                    $classe = $this->getDoctrine()->getRepository(Niveau::class)->find($sessionN);
                    $ue = new Ue();
                    $ue->setFiliere($filiere);
                    $ue->setNiveau($classe);
                    $ue->setMatiere($matiere);
                    $ue->setSemestre($semestre);
                    $ue->setCreatedAt(new \datetime());
                    $ue->setUser($user);
                    $manager = $managerRegistry->getManager();
                    $manager->persist($ue);
                    $manager->flush();
                }
            }
        }

        return $this->render('matieres/essaie.html.twig', [
            'mr' =>  $matiereRepository->matiereUserPasEncoreUe($user,$sessionF,$sessionN,$sessionSe),
        ]);
    }

    /**
     * on cree des ues pour les filieres et niveaux et on precise le semestre tous enregistrees dans la session
     * @Route("transfert", name="transfert")
     */
    public function transfert(SessionInterface $session,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,MatiereRepository $matiereRepository , SemestreRepository $semestreRepository ,Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        $sessionF=$session->get('filiere',[]);
        $sessionSe=$session->get('semestre',[]);
        $sessionN=$session->get('niveau',[]);
        if (!empty($sessionF) && !empty($sessionSe) && !empty($sessionN) && !empty($request->request->get('ue'))) {
            $matiere=$matiereRepository->find($request->request->get('ue'));    
            //dd($session->get('filiere'),$session->get('niveau'));
            $filiere=$filiereRepository->find($sessionF);
            $semestre=$semestreRepository->find($sessionSe);
            $niveau=$niveauRepository->find($sessionN);
            $ue=new Ue();
            $ue->setFiliere($filiere);
            $ue->setSemestre($semestre);
            $ue->setUser($user);
            $ue->setNiveau($niveau);
            $ue->setMatiere($matiere);
            $ue->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($ue);
            $manager->flush();
            
            return $this->redirectToRoute('matieres_transfert');
        }
        $filieresPasEncoreUe=$filiereRepository->find($sessionF);
        $niveauxPasEncoreUe=$niveauRepository->find($sessionN);
        $semestrePasEncoreUe=$semestreRepository->find($sessionSe);
       
        return $this->render('matieres/transfert_matiere.html.twig', [
            'controller_name' => 'MatieresController',
            'filieres'=>$filiereRepository->filieresUser($user),
            'matieres'=>$matiereRepository->matiereUserPasEncoreUe($user,$filieresPasEncoreUe,$niveauxPasEncoreUe,$semestrePasEncoreUe),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * on choisi la filiere et le niveau  pour consulter des cours
     * @Route("choix_filiereEtNiveauUes_liste", name="choix_filiereEtNiveauUes_liste")
     */
    function choix_filiereEtNiveauUes_liste(SessionInterface $session,Request $request,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        if (!empty($request->request->all())) {
            $filiere=$filiereRepository->find($request->request->get("filiere"));
            $niveau=$niveauRepository->find($request->request->get('niveau'));
            $get_filiere=$session->get('filiere',[]);
            if (!empty($get_filiere)) {
              $session->set('filiere',$filiere);
              $session->set('niveau',$niveau);
            }
            //dd($session);
            $session->set('filiere',$filiere);
            $session->set('niveau',$niveau);
            return $this->redirectToRoute('matieres_liste_Ues');
        }
        return $this->render('matieres/filiereNiveauUes_liste.html.twig',[
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * on consulte les donnees des ues
     * @Route("liste_Ues", name="liste_Ues")
     */
    public function liste_Ues (SessionInterface $session,FiliereRepository $filiereRepository,NiveauRepository $niveauRepository,UeRepository $ueRepository,Request $request)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on consulte les données
        $sessionF=$session->get('filiere',[]);
        $sessionN=$session->get('niveau',[]);
        $uesUserFiliereNiveau=$ueRepository->uesFiliereNiveau($sessionF,$sessionN);
        return $this->render('matieres/liste_Ues.html.twig', [
            'controller_name' => 'MatieresController',
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'uesUserFiliereNiveau'=>$uesUserFiliereNiveau
        ]);
    }


    /**
     * on supprime les ues
     * @Route("ue_suppression/{id}", name="ue_suppression")
     */
    public function suppression_ue(Ue $ue, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        //sinon on supprime les données
        $manager = $end->getManager();
        $manager->remove($ue);
        $manager->flush();

        return $this->redirectToRoute('matieres_liste_Ues');
        
    }
    
}
