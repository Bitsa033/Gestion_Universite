<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\NotesEtudiant;
use App\Form\EditNotesType;
use App\Form\NotesEtudiantType;
use App\Repository\NotesEtudiantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("notes_etudiant_")
 */
class NotesEtudiantController extends AbstractController
{
    /**
     * @Route("index", name="notes_etudiant_index", methods={"GET"})
     */
    public function index(NotesEtudiantRepository $notesEtudiantRepository): Response
    {
        return $this->render('notes_etudiant/index.html.twig', [
            'notes_etudiants' => $notesEtudiantRepository->findAll(),
        ]);
    }

    /**
     * @Route("choixFiliereNiveauxSemestreNouvelleNote", name="choixFiliereNiveauxSemestreNouvelleNote")
     */
    public function choixFiliereNiveauxSemestreNouvelleNote(): Response
    {
        return $this->render('index.html.twig', []);
    }

    /**
     * @Route("new", name="notes_etudiant_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on traite le formulaire des notes
        $notesEtudiant = new NotesEtudiant();
        $form = $this->createForm(NotesEtudiantType::class, $notesEtudiant);
        $notesEtudiant->setCreatedAt(new \datetime());
        $notesEtudiant->setUser($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($notesEtudiant);
            $entityManager->flush();

            return $this->redirectToRoute('notes_etudiant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('notes_etudiant/new.html.twig', [
            'notes_etudiant' => $notesEtudiant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("{id}", name="notes_etudiant_show", methods={"GET"})
     */
    public function show(NotesEtudiant $notesEtudiant): Response
    {
        return $this->render('notes_etudiant/show.html.twig', [
            'notes_etudiant' => $notesEtudiant,
        ]);
    }

    /**
     * @Route("{id}/edit", name="notes_etudiant_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, NotesEtudiant $notesEtudiant, EntityManagerInterface $entityManager): Response
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on modifie les données
        if (!empty($request->request->get('moyenne'))) {
            $notesEtudiant->setMoyenne($request->request->get('moyenne'));
            $notesEtudiant->setCreatedAt(new \DateTime());
    
            $entityManager->flush();

            return $this->redirectToRoute('notes_etudiant_index');
        }

        return $this->render('notes_etudiant/edit.html.twig', [
            'notes_etudiant' => $notesEtudiant,
        ]);
    }
    /**
     * @Route("miseAjour{id}_edit", name="miseAjour", methods={"GET", "POST"})
     */
    public function miseAjour(Request $request, NotesEtudiant $notes_etudiant, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on modifie les données
        if (!empty($request->request->get('moyenne'))) {
            $notes_etudiant->setMoyenne($request->request->get('moyenne'));
            $notes_etudiant->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->flush();

            return $this->redirectToRoute('notes_etudiant');
        }

        $repos = $this->getDoctrine()->getRepository(Matiere::class);
        $matieres = $repos->findAll();
        return $this->render('notes_etudiant/edit.html.twig', [
            
            'notes_etudiant' => $notes_etudiant,
        ]);
    }

    /**
     * @Route("{id}", name="notes_etudiant_delete", methods={"POST"})
     */
    public function delete(Request $request, NotesEtudiant $notesEtudiant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $notesEtudiant->getId(), $request->request->get('_token'))) {
            $entityManager->remove($notesEtudiant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('notes_etudiant_index', [], Response::HTTP_SEE_OTHER);
    }
}
