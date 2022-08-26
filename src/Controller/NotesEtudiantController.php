<?php

namespace App\Controller;

use App\Entity\NotesEtudiant;
use App\Repository\EtudiantRepository;
use App\Repository\FiliereRepository;
use App\Repository\InscriptionRepository;
use App\Repository\NiveauRepository;
use App\Repository\NotesEtudiantRepository;
use App\Repository\SemestreRepository;
use App\Repository\UeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("notes_etudiant_")
 */
class NotesEtudiantController extends AbstractController
{
    /**
     * @Route("index", name="notes_etudiant_index", methods={"GET"})
     */
    public function index(EtudiantRepository $etudiantRepository,SessionInterface $session,InscriptionRepository $inscriptionRepository, NotesEtudiantRepository $notesEtudiantRepository,UeRepository $cours, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, SemestreRepository $semestreRepository ): Response
    {
        $user = $this->getUser();

        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        $sessionInsc=$session->get('inscription');
        $noteE=$notesEtudiantRepository->notesEtudiant($user);

        
        return $this->render('notes_etudiant/index.html.twig', [
            'coursSemestre'=>$cours->findBy([
                'filiere'=>$sessionF,
                'niveau'=>$sessionN,
                'semestre'=>$sessionSe
            ]),
            'filiere'=>$filiereRepository->find($sessionF),
            'classe'=>$niveauRepository->find($sessionN),
            'semestre'=>$semestreRepository->find($sessionSe),
            'inscription'=>$inscriptionRepository->find($sessionInsc),
            'etudiant' => $etudiantRepository->findBy(['id'=>$sessionInsc]),
            'matieres'=>$notesEtudiantRepository->findBy(
                ['inscription'=>$sessionInsc,
            ]),
            'notes' => $noteE,
        ]);
    }

    /**
     * @Route("show_{id}", name="notes_etudiant_show")
     */
    public function show(NotesEtudiant $notesEtudiant): Response
    {
        return $this->render('notes_etudiant/show.html.twig', [
            'notes_etudiant'=>$notesEtudiant
        ]);
    }

    /**
     * @Route("edit_{id}", name="notes_etudiant_edit")
     */
    public function edit(NotesEtudiant $notesEtudiant, ManagerRegistry $managerRegistry, Request $request): Response
    {
        if (!empty($request->request->get('moyenne'))) {
            $notesEtudiant->setMoyenne($request->request->get('moyenne'));
            $notesEtudiant->setCreatedAt(new \DateTime());
            $manager = $managerRegistry->getManager();
            $manager->flush();

            return $this->redirectToRoute('notes_etudiant_index');
        } 

        return $this->render('notes_etudiant/edit.html.twig', [
            'notes_etudiant'=>$notesEtudiant
        ]);
    }

    /**
     * @Route("delete", name="notes_etudiant_delete")
     */
    public function delete(): Response
    {
        return $this->render('notes_etudiant/edit.html.twig', []);
    }

    /**
     * @Route("impression", name="notes_etudiant_impression", methods={"GET"})
     */
    public function impression(SessionInterface $session,InscriptionRepository $inscriptionRepository, NotesEtudiantRepository $notesEtudiantRepository,UeRepository $cours, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, SemestreRepository $semestreRepository ): Response
    {
        $user = $this->getUser();

        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        $sessionInsc=$session->get('inscription');
        //dd($session);
        $notesUserFiliereNiveau = $notesEtudiantRepository->notesEtudiantUser($user, $sessionF, $sessionN,$sessionSe,$sessionInsc);
        return $this->render('notes_etudiant/impression.html.twig', [
            'coursSemestre'=>$cours->findBy([
                'filiere'=>$sessionF,
                'niveau'=>$sessionN,
                'semestre'=>$sessionSe
            ]),
            'filiere'=>$filiereRepository->find($sessionF),
            'classe'=>$niveauRepository->find($sessionN),
            'semestre'=>$semestreRepository->find($sessionSe),
            'inscription'=>$inscriptionRepository->find($sessionInsc)
        ]);
    }


}
