<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Filiere;
use App\Entity\Etudiant;
use App\Entity\Niveau;
use App\Entity\Inscription;
use App\Entity\Matiere;
use App\Repository\InscriptionRepository;
use App\Entity\Ue;

class UniversgController extends AbstractController
{
    /**
     * @Route("/universg", name="universg")
     */
    public function index(): Response
    {
        return $this->render('universg/index.html.twig', [
            'controller_name' => 'UniversgController',
        ]);
    }

    /**
     * Insertion et affichage des filieres
     * @Route("filiere", name="filiere")
     */
    public function filiere(Request $request, ManagerRegistry $end)
    {
        //insertion de la filiere si la request n'est pas vide
        if (!empty($request->request->get('nom_f')) && !empty($request->request->get('abbr_filiere'))) {
            $filiere=new Filiere();
            $filiere->setNom($request->request->get('nom_f'));
            $filiere->setSigle($request->request->get('abbr_filiere'));
            $filiere->setCreatedAt(new \datetime);
            $manager = $end->getManager();
            $manager->persist($filiere);
            $manager->flush();

            return $this->redirectToRoute('filiere');
        } 
         //affiche des filieres
        $repos=$this->getDoctrine()->getRepository(Filiere::class);
        $filieres = $repos->findAll();
        return $this->render('universg/filiere.html.twig', [
            'controller_name' => 'UniversgController',
            'filieres'=>$filieres
        ]);
    }

    /**
     * Suppression des filieres
     * @Route("filiere/delete/{id}", name="delete_filiere")
     */
    public function deleteFiliere (Filiere $filiere, ManagerRegistry $end)
    {
        $manager = $end->getManager();
        $manager->remove($filiere);
        $manager->flush();

        return $this->redirectToRoute('filiere');
        
        return $this->render('universg/filiere.html.twig', [
            'controller_name' => 'UniversgController'
        ]);
    }

    /**
     * Insertion et affichage des niveaux
     * @Route("niveau", name="niveau")
     */
    public function niveau (Request $request, ManagerRegistry $end)
    {
         //insertion du niveau si la request n'est pas vide
        if (!empty($request->request->get('nom_niv'))) {
            $niveau=new Niveau();
            $niveau->setNom($request->request->get('nom_niv'));
            $niveau->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($niveau);
            $manager->flush();

            return $this->redirectToRoute('niveau');
        } 
          //affiche des niveaux
        $repos=$this->getDoctrine()->getRepository(Niveau::class);
        $niveaux = $repos->findAll();
        return $this->render('universg/niveau.html.twig', [
            'controller_name' => 'UniversgController',
            'niveaux'=>$niveaux
        ]);
    }

    /**
     * Suppression des niveaux
     * @Route("niveau/delete/{id}", name="delete_niveau")
     */
    public function deleteNiveau (Niveau $niveau, ManagerRegistry $end)
    {
        $manager = $end->getManager();
        $manager->remove($niveau);
        $manager->flush();

        return $this->redirectToRoute('niveau');
        
        return $this->render('universg/niveau.html.twig', [
            'controller_name' => 'UniversgController'
        ]);
    }

    /**
     * @Route("find_niveau/{id}", name="find_niveau")
     */
    public function find_niveau(Niveau $niveau)
    {
        //affichage d'un niveau particulier
        return $this->render('universg/find_niv.html.twig', [
            'controller_name' => 'UniversgController',
            'niveau'=>$niveau
        ]);
    }

    /**
     * Creation des etudiants
     * @Route("creer_etudiant", name="create")
     */
    public function creer_etudiant (Request $request,ManagerRegistry $end_e)
    {
         //insertion de l'etudiant si la request n'est pas vide
       if (!empty($request->request->get('filiere')) && !empty($request->request->get('nom_et'))) {
          $repof=$this->getDoctrine()->getRepository(Filiere::class);
          $filiere = $repof->find($request->request->get("filiere"));
          
          $etudiant=new Etudiant();
          $etudiant->setNom($request->request->get("nom_et"));
          $etudiant->setprenom($request->request->get("prenom_et"));
          $etudiant->setSexe($request->request->get("sexe_et"));
          $etudiant->setFiliere($filiere);
          $etudiant->setCreatedAt(new \DateTime());
          //$manager=$mg->getManager();
          $manager = $end_e->getManager();
          
          $manager->persist($etudiant);
          $manager->flush();

          return $this->redirectToRoute('etudiant');
       }
       //affichage des filieres pour l'enregistrement
       $reposf=$this->getDoctrine()->getRepository(Filiere::class);
       $filieres = $reposf->findAll();

        return $this->render('universg/creation.html.twig', [
            'controller_name' => 'UniversgController',
            'filieres'=>$filieres
        ]);
    }

    /**
     * Affichage des etudiants et leurs niveaux
     * @Route("etudiant", name="etudiant")
     */
    public function etudiant (Request $request,ManagerRegistry $end_e)
    {
        
        $reposf=$this->getDoctrine()->getRepository(Filiere::class);
        $filieres = $reposf->findAll();
        $repos=$this->getDoctrine()->getRepository(Etudiant::class);
        $etudiants = $repos->findAll();
        return $this->render('universg/etudiant.html.twig', [
            'controller_name' => 'UniversgController',
            'filieres'=>$filieres,
            'etudiants'=>$etudiants
        ]);
    }
    
    /**
     * Suppression des etudiants
     * @Route("etudiant/delete/{id}", name="delete_etudiant")
     */
    public function deleteEtudiant (Etudiant $etudiant, ManagerRegistry $end)
    {
        $manager = $end->getManager();
        $manager->remove($etudiant);
        $manager->flush();

        return $this->redirectToRoute('etudiant');
        
        return $this->render('universg/etudiant.html.twig', [
            'controller_name' => 'UniversgController'
        ]);
    }

    /**
     * Inscription des etudiants
     * @Route("inscription/{id}", name="inscription")
     */
    public function inscription(Etudiant $in, Request $request,ManagerRegistry $end_e )
         {
             if (!empty($request->request->get('niveau'))) {
        
            $repon=$this->getDoctrine()->getRepository(Niveau::class);
            $niveau = $repon->find($request->request->get("niveau"));

            $inscription=new Inscription();
            $inscription->setEtudiant($in);
            $inscription->setNiveau($niveau);
            $inscription->setCreatedAt(new \DateTime());
            //$manager=$mg->getManager();
            $manager = $end_e->getManager();
            $manager->persist($inscription);
            $manager->flush();
  
            return $this->redirectToRoute('etudiants_niveau');
        }

        $reposn=$this->getDoctrine()->getRepository(Niveau::class);
        $niveaux = $reposn->findAll();
        $reposn=$this->getDoctrine()->getRepository(Etudiant::class);
        $etudiant = $reposn->findAll();

        return $this->render('universg/inscription.html.twig', [
            'controller_name' => 'UniversgController',
            'niveaux'=>$niveaux,
            'etudiants'=>$etudiant,
            'etudiant'=>$in
        ]);
    }

    /**
     * @Route("etudiants_niveau", name="etudiants_niveau")
     */
    public function etudiants_niveau(): Response
    {
        $reposf=$this->getDoctrine()->getRepository(Inscription::class)->findAll();
    //      $inc= $this->createQueryBuilder('select * from inscription')
    //    ->andWhere('inscription.niveau_id = :val')
    //    ->setParameter('val', $id)
    //    ->getQuery()
    //    ->getOneOrNullResult();
    
        return $this->render('universg/niv_etu.html.twig', [
            'controller_name' => 'UniversgController',
            'inc'=>$reposf
        ]);
    }

    /**
     * @Route("matiere", name="matiere")
     */
    public function matiere (Request $request, ManagerRegistry $end)
    {
        
        if (!empty($request->request->get('nom_mat'))) {
            $matiere=new Matiere();
            $matiere->setNom($request->request->get('nom_mat'));
            $matiere->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($matiere);
            $manager->flush();

            return $this->redirectToRoute('matiere');
        } 

        $repos=$this->getDoctrine()->getRepository(Matiere::class);
        $matiere = $repos->findAll();
        return $this->render('universg/matiere.html.twig', [
            'controller_name' => 'UniversgController',
            'matieres'=>$matiere
        ]);
    }

    /**
     * Suppression des matieres
     * @Route("matiere/delete/{id}", name="delete_matiere")
     */
    public function deleteMatiere (Matiere $matiere, ManagerRegistry $end)
    {
        $manager = $end->getManager();
        $manager->remove($matiere);
        $manager->flush();

        return $this->redirectToRoute('matiere');
        
        return $this->render('universg/matiere.html.twig', [
            'controller_name' => 'UniversgController'
        ]);
    }

    /**
     * Affectation des matieres à des filieres et niveaux
     * @Route("matiere/transfert/{id}", name="transfert_matiere")
     */
    public function transfert_matiere (Request $request, ManagerRegistry $end, Matiere $matiere)
    {
        if (!empty($request->request->get('filiere')) && !empty($request->request->get('niveau'))) {
            $filiere=$this->getDoctrine()->getRepository(Filiere::class)->find($request->request->get('filiere'));
            $niveau=$this->getDoctrine()->getRepository(Niveau::class)->find($request->request->get('niveau'));
            
            $ue=new Ue();
            $ue->setFiliere($filiere);
            $ue->setNiveau($niveau);
            $ue->setMatiere($matiere);
            $ue->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($ue);
            $manager->flush();
  
            return $this->redirectToRoute('matiere');
        } 
        $filieres=$this->getDoctrine()->getRepository(Filiere::class)->findAll();
        $niveaux=$this->getDoctrine()->getRepository(Niveau::class)->findAll();
        return $this->render('universg/transfert_matiere.html.twig', [
            'controller_name' => 'UniversgController',
            'filieres'=>$filieres,
            'matiere'=>$matiere,
            'niveaux'=>$niveaux
        ]);
    }

    /**
     * Affichage des unites d'enseignement
     * @Route("ue", name="ue")
     */
    public function ue ()
    {
        $repostn=$this->getDoctrine()->getRepository(Ue::class)->findAll();
        return $this->render('universg/ue.html.twig', [
            'controller_name' => 'UniversgController',
            'ues'=>$repostn
        ]);
    }
}
