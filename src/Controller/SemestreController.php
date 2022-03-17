<?php

namespace App\Controller;

use App\Entity\Semestre;
use App\Form\SemestreType;
use App\Repository\SemestreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("semestre_", name="semestre_")
 */
class SemestreController extends AbstractController
{
    /**
     * @Route("index", name="index", methods={"GET","POST"})
     */
    public function index(SemestreRepository $semestreRepository,Request $request, EntityManagerInterface $entityManager): Response
    {
        
        if (!empty($request->request->get("nom_s"))) {
            $semestre = new Semestre();
            $semestre->setNom($request->request->get("nom_s"));
            $entityManager->persist($semestre);
            $entityManager->flush();

            return $this->redirectToRoute('semestre_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('semestre/index.html.twig', [
            'semestres' => $semestreRepository->findAll(),
        ]);
    }

    /**
     * @Route("new", name="new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $semestre = new Semestre();
        $form = $this->createForm(SemestreType::class, $semestre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($semestre);
            $entityManager->flush();

            return $this->redirectToRoute('semestre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('semestre/new.html.twig', [
            'semestre' => $semestre,
            'form' => $form->createView(),
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
