<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MatieresController extends AbstractController
{
    /**
     * @Route("/matieres", name="matieres")
     */
    public function index(): Response
    {
        return $this->render('matieres/index.html.twig', [
            'controller_name' => 'MatieresController',
        ]);
    }
}
