<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Dompdf\Dompdf;

class GenepdfController extends AbstractController
{
    #[Route('/genepdf', name: 'app_genepdf')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $posts=$entityManager->getRepository(Post::class)->findAll();
       
/*         return $this->render('genepdf/index.html.twig', [
            'users' => $users,
        ]); */

        $html =  $this->renderView('genepdf/index.html.twig', ['posts'=>$posts]);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();
         
        return new Response (
            $dompdf->stream('resume', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );

    }
}
