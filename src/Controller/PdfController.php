<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfController extends AbstractController
{
    /**
     * @Route("/pdf", name="pdf")
     */
    public function index(): Response
    {
        $pdfOptions= new Options();
        $pdfOptions->set('defaultFont','Arial');

        $dompdf=new Dompdf($pdfOptions);

        $html=$this->renderView('pdf/index.html.twig',[
            'titre'=>'Bienvenue sur notre test du premier pdf'
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4','portrait');
        $dompdf->render();

        $dompdf->stream('monpdf.pdf',[
            "Attachment"=>true
        ]);


        return $this->render('pdf/index.html.twig', [
            'controller_name' => 'PdfController',
        ]);
    }
}
