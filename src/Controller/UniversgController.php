<?php

namespace App\Controller;

use App\Entity\Ue;
use App\Entity\Niveau;
use App\Entity\Filiere;
use App\Entity\Matiere;
use App\Entity\Etudiant;
use App\Entity\Inscription;
use App\Repository\UeRepository;
use App\Repository\FiliereRepository;
use App\Repository\EtudiantRepository;
use App\Repository\InscriptionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * @Route("filieres", name="filieres")
     */
    public function filiere(Request $request, ManagerRegistry $end)
    {
        //insertion de la filiere si la request n'est pas vide
        if (!empty($request->request->get('nom_f')) && !empty($request->request->get('abbr_filiere'))) {
            $filiere=new Filiere();
            $filiere->setNom(ucfirst($request->request->get('nom_f')));
            $filiere->setSigle(strtoupper($request->request->get('abbr_filiere')));
            $filiere->setCreatedAt(new \datetime);
            $manager = $end->getManager();
            $manager->persist($filiere);
            $manager->flush();

            return $this->redirectToRoute('filieres');
        } 
         //affiche des filieres
        $repos=$this->getDoctrine()->getRepository(Filiere::class);
        $filieres = $repos->findAll();
        return $this->render('universg/filieres.html.twig', [
            'controller_name' => 'UniversgController',
            'filieres'=>$filieres
        ]);
    }

    /**
     * Suppression des filieres
     * @Route("filiere/suppression/{id}", name="suppression_filiere")
     */
    public function suppression_filiere (Filiere $filiere, ManagerRegistry $end)
    {
        $manager = $end->getManager();
        $manager->remove($filiere);
        $manager->flush();

        return $this->redirectToRoute('filieres');
        
        return $this->render('universg/filieres.html.twig', [
            'controller_name' => 'UniversgController'
        ]);
    }

    /**
     * Insertion et affichage des niveaux
     * @Route("niveaux", name="niveaux")
     */
    public function niveau (Request $request, ManagerRegistry $end)
    {
         //insertion du niveau si la request n'est pas vide
        if (!empty($request->request->get('nom_niv'))) {
            $niveau=new Niveau();
            $niveau->setNom(strtoupper($request->request->get('nom_niv')));
            $niveau->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($niveau);
            $manager->flush();

            return $this->redirectToRoute('niveaux');
        } 
          //affiche des niveaux
        $repos=$this->getDoctrine()->getRepository(Niveau::class);
        $niveaux = $repos->findAll();
        return $this->render('universg/niveaux.html.twig', [
            'controller_name' => 'UniversgController',
            'niveaux'=>$niveaux
        ]);
    }

    /**
     * Suppression des niveaux
     * @Route("niveau/suppression/{id}", name="suppression_niveau")
     */
    public function suppression_niveau (Niveau $niveau, ManagerRegistry $end)
    {
        $manager = $end->getManager();
        $manager->remove($niveau);
        $manager->flush();

        return $this->redirectToRoute('niveau');
        
        return $this->render('universg/niveaux.html.twig', [
            'controller_name' => 'UniversgController'
        ]);
    }

    /**
     * @Route("consultation/niveau/{id}", name="consultation_niveau")
     */
    public function consultation_niveau(Niveau $niveau)
    {
        //affichage d'un niveau particulier
        return $this->render('universg/consultation_niveau.html.twig', [
            'controller_name' => 'UniversgController',
            'niveau'=>$niveau
        ]);
    }

    /**
     * Creation des etudiants
     * @Route("create", name="creation_etudiants")
     */
    public function creation_etudiants (Request $request,ManagerRegistry $end_e)
    {
        $nb_row=1;
        if (!empty($request->request->get('nb_row'))) {
            $request->query->set('nb_row',$request->request->get('nb_row'));
           // return new Response($request->query->get('nb_row'));
            $nb_row = $request->query->get('nb_row');
        }

        // for ($i=1; $i <$nb_row ; $i++) { 
          
        // }
        // $request->query->set('nb_row',1);
        // return new Response($request->query->get('nb_row'));
        //insertion de l'etudiant si la request n'est pas vide
       if (!empty($request->request->get('nom_et')) && 
       !empty($request->request->get('prenom_et')) &&
       !empty($request->request->get('sexe_et'))) {
            //on enregistre l'etudiant
          $etudiant=new Etudiant();
          $etudiant->setNom(strtoupper($request->request->get("nom_et")));
          $etudiant->setprenom(ucfirst($request->request->get("prenom_et")));
          $etudiant->setSexe(strtoupper($request->request->get("sexe_et")));
          $etudiant->setCreatedAt(new \DateTime());
          $manager = $end_e->getManager();
          $manager->persist($etudiant);
          $manager->flush();

          return $this->redirectToRoute('etudiants');
       }
        return $this->render('universg/creation_etudiants.html.twig', [
            'controller_name' => 'UniversgController',
            'nb_row'=>$nb_row
        ]);
    }

    /**
     * Affichage des etudiants enregistrés avec leurs filieres
     * @Route("etudiants", name="etudiants")
     */
    public function etudiants (EtudiantRepository $etudiants,FiliereRepository $filieres)
    {
        return $this->render('universg/etudiants.html.twig', [
            'controller_name' => 'UniversgController',
            'filieres'=>$filieres->findAll(),
            'etudiants'=>$etudiants->findAll()
        ]);
    }
    
    /**
     * Suppression des etudiants
     * @Route("etudiant/suppression/{id}", name="suppression_etudiant")
     */
    public function suppression_etudiant (Etudiant $etudiant, ManagerRegistry $end)
    {
        $manager = $end->getManager();
        $manager->remove($etudiant);
        $manager->flush();

        return $this->redirectToRoute('etudiants');
        
        // return $this->render('universg/etudiants.html.twig', [
        //     'controller_name' => 'UniversgController'
        // ]);
    }

    /**
     * Inscription des etudiants
     * @Route("etudiants/inscription/{id}", name="inscription_etudiants")
     */
    public function inscription_etudiants(Etudiant $student, Request $request,ManagerRegistry $end_e )
        {
        if (!empty($request->request->get('niveau'))) {
    
            $niveau = $this->getDoctrine()->getRepository(Niveau::class)->find($request->request->get("niveau"));
            $filiere = $this->getDoctrine()->getRepository(Filiere::class)->find($request->request->get("filiere"));

            $inscription=new Inscription();
            $inscription->setEtudiant($student);
            $inscription->setFiliere($filiere);
            $inscription->setNiveau($niveau);
            $inscription->setCreatedAt(new \DateTime());
            //$manager=$mg->getManager();
            $manager = $end_e->getManager();
            $manager->persist($inscription);
            $manager->flush();

            return $this->redirectToRoute('liste_inscriptions_etudiants');
        }

        $niveaux = $this->getDoctrine()->getRepository(Niveau::class)->findAll();
        $etudiants = $this->getDoctrine()->getRepository(Etudiant::class)->findAll();
        $filieres = $this->getDoctrine()->getRepository(Filiere::class)->findAll();
        return $this->render('universg/inscription_etudiants.html.twig', [
            'controller_name' => 'UniversgController',
            'niveaux'=>$niveaux,
            'filieres'=>$filieres,
            'etudiants'=>$etudiants,
            'etudiant'=>$student
        ]);
    }

    /**
     * @Route("etudiants/inscriptions/liste", name="liste_inscriptions_etudiants")
     */
    public function liste_inscriptions_etudiants(Request $request, InscriptionRepository $repo)
    {
        $repostn=$this->getDoctrine()->getRepository(Ue::class)->findAll();
        $filieres=$this->getDoctrine()->getRepository(Filiere::class)->findAll();
        $niveaux=$this->getDoctrine()->getRepository(Niveau::class)->findAll();
        $search=$repo->search($request->request->get('niveau'),$request->request->get('filiere'));
        return $this->render('universg/liste_inscriptions_etudiants.html.twig', [
            'controller_name' => 'UniversgController',
            'ues'=>$repostn,
            'filieres'=>$filieres,
            'niveaux'=>$niveaux,
            'recherche'=>$search
        ]);
    }

    /**
     * Inscription des etudiants
     * @Route("noter_etudiant/{id}", name="noter_etudiant")
     */
    public function noter_etudiants(Inscription $student, Request $request,ManagerRegistry $end_e )
        {
        if (!empty($request->request->get('niveau'))) {
    
            $niveau = $this->getDoctrine()->getRepository(Niveau::class)->find($request->request->get("niveau"));
            $filiere = $this->getDoctrine()->getRepository(Filiere::class)->find($request->request->get("filiere"));

            // $inscription=new Inscription();
            // $inscription->setEtudiant($student);
            // $inscription->setFiliere($filiere);
            // $inscription->setNiveau($niveau);
            // $inscription->setCreatedAt(new \DateTime());
            //$manager=$mg->getManager();
            // $manager = $end_e->getManager();
            // $manager->persist($inscription);
            // $manager->flush();

            return $this->redirectToRoute('liste_inscriptions_etudiants');
        }

        $matieres = $this->getDoctrine()->getRepository(Matiere::class)->findAll();
        $etudiants = $this->getDoctrine()->getRepository(Etudiant::class)->findAll();
        $filieres = $this->getDoctrine()->getRepository(Filiere::class)->findAll();
        return $this->render('universg/noter_etudiant.html.twig', [
            'controller_name' => 'UniversgController',
            'matieres'=>$matieres,
            'etudiants'=>$etudiants,
            'inscription'=>$student
        ]);
    }

    /**
     * @Route("matieres", name="matieres")
     */
    public function matieres (Request $request, ManagerRegistry $end)
    {
        if (!empty($request->request->get('nom_mat'))) {
            $matiere=new Matiere();
            $matiere->setNom($request->request->get('nom_mat'));
            $matiere->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($matiere);
            $manager->flush();

            return $this->redirectToRoute('matieres');
        } 

        $repos=$this->getDoctrine()->getRepository(Matiere::class);
        $matieres = $repos->findAll();
        return $this->render('universg/matieres.html.twig', [
            'controller_name' => 'UniversgController',
            'matieres'=>$matieres
        ]);
    }

    /**
     * @Route("matiere_edit/{id}", name="matiere_edit")
     */
    public function matiere_edit (Matiere $matiere, Request $request, ManagerRegistry $end)
    {
        if (!empty($request->request->get('nom_mat'))) {
            $matiere->setNom($request->request->get('nom_mat'));
            $matiere->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->flush();

            return $this->redirectToRoute('matieres');
        } 

        $repos=$this->getDoctrine()->getRepository(Matiere::class);
        $matieres = $repos->findAll();
        return $this->render('universg/matiere_edit.html.twig', [
            'controller_name' => 'UniversgController',
            'matieres'=>$matieres,
            'matiere'=>$matiere
        ]);
    }

    /**
     * Suppression des matieres
     * @Route("matiere/suppression/{id}", name="suppression_matiere")
     */
    public function suppression_matiere (Matiere $matiere, ManagerRegistry $end)
    {
        $manager = $end->getManager();
        $manager->remove($matiere);
        $manager->flush();

        return $this->redirectToRoute('matieres');
        
        // return $this->render('universg/matieres.html.twig', [
        //     'controller_name' => 'UniversgController'
        // ]);
    }

    /**
     * Affectation des matieres à des filieres et niveaux
     * @Route("matiere/transfert/{id}", name="transfert_matiere")
     */
    public function transfert_matiere (Request $request, ManagerRegistry $end, Matiere $matiere)
    {
        if (!empty($request->request->get('niveau'))) {
            $filiere=$this->getDoctrine()->getRepository(Filiere::class)->findAll();
            $niveau=$this->getDoctrine()->getRepository(Niveau::class)->find($request->request->get('niveau'));

            foreach ($filiere as $filiere2) {
                
                $ue=new Ue();
                $ue->setFiliere($filiere2);
                $ue->setNiveau($niveau);
                $ue->setMatiere($matiere);
                $ue->setCreatedAt(new \DateTime());
                $manager = $end->getManager();
                $manager->persist($ue);
                $manager->flush();
            }
            
  
            return $this->redirectToRoute('ue');
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
    public function ue (Request $request, UeRepository $repo)
    {
        $repostn=$this->getDoctrine()->getRepository(Ue::class)->findAll();
        $filieres=$this->getDoctrine()->getRepository(Filiere::class)->findAll();
        $niveaux=$this->getDoctrine()->getRepository(Niveau::class)->findAll();
        $search=$repo->search($request->request->get('niveau'),$request->request->get('filiere'));
        return $this->render('universg/ue.html.twig', [
            'controller_name' => 'UniversgController',
            'ues'=>$repostn,
            'filieres'=>$filieres,
            'niveaux'=>$niveaux,
            'recherche'=>$search
        ]);
    }
}
