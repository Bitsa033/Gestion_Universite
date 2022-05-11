<?php

namespace App\Controller;

use App\Entity\NotesEtudiant;
use App\Entity\Semestre;
use App\Repository\InscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MatiereRepository;
use App\Repository\UeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/notes_", name="notes_")
 */
class NotesController extends AbstractController
{
    /**
     * @Route("/notes", name="notes")
     */
    public function index(): Response
    {
        return $this->render('notes/index.html.twig', [
            'controller_name' => 'NotesController',
        ]);
    }

    /**
     * @Route("s", name="s")
     */
    public function t(ManagerRegistry $managerRegistry,Request $request,SessionInterface $session,InscriptionRepository $inscriptionRepository,UeRepository $ueRepository): Response
    {
        $user=$this->getUser();
        $sessionSe = $this->getDoctrine()->getRepository(Semestre::class)->find(1);
        $inscriptions=$inscriptionRepository->findAll();
        foreach ($inscriptions as $i => $value) {
        
            if (!empty($request->request->get("cours")) ) {
                $etudiant = $inscriptionRepository->find($request->request->get("nom").$value->getId());
                $cours = $ueRepository->find($request->request->get("cours"));
                $moyenne = $request->request->get("moyenne").$value->getId();
                dd($moyenne);

                // $notesEtudiant = new NotesEtudiant();
                // $notesEtudiant->setInscription($etudiant);
                // $notesEtudiant->setUe($cours);
                // $notesEtudiant->setMoyenne($moyenne);
                // $notesEtudiant->setSemestre($sessionSe);
                // $notesEtudiant->setCreatedAt(new \datetime());
                // $notesEtudiant->setUser($user);
                // $manager=$managerRegistry->getManager();
                // $manager->persist($notesEtudiant);
                // $manager->flush();
            }
        }



        return $this->render('notes_etudiant/essaie.html.twig', [
            'mr'=>$ueRepository->findAll(),
            'm'=>$inscriptions
        ]);
    }
}
