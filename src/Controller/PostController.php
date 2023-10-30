<?php

namespace App\Controller;
use App\Entity\Post; 
use App\Entity\Comment; 
use App\Form\CommentType; 
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Knp\Component\Pager\PaginatorInterface;
class PostController extends AbstractController
{
    #[Route('/', name: 'app_post')]
    public function index( EntityManagerInterface $entityManager,PaginatorInterface $paginator, Request $request ): Response
    {
       
       $query =$entityManager->  getRepository(Post::class)->findAll();
       $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        3/*limit per page*/
    );
        return $this->render('post/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    #[Route('/newpost', name:'app_newpost')]
    
    
    public function newpost(Request $request, EntityManagerInterface $entityManager,  SluggerInterface $slugger){
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){

            $brochureFile = $form->get('image_name')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('post_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $post->setImageName($newFilename);
            }

            $user=$this->getUser();
            $post->setUser($user);
            $entityManager->persist($post);
            $entityManager->flush();
            

            return $this->redirectToRoute('app_newpost');
        }
        
        return $this->render ('post/newpost.html.twig',[
                'form' => $form->createView(),
                
            ]);
    }
  
    #[Route('/viewpost/{id}', name:'app_viewpost')]
    public function viewpost(Request $request, EntityManagerInterface $entityManager, $id){
        $post = $entityManager->getRepository(Post:: class)->find($id);
        $comment =new comment();
        $form =$this->createForm(CommentType::class,$comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form -> isValid()){

            $comment->setUser($this->getUser());
            $comment->setCreationDate(new \DateTime());
            $comment->setPost($post);
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('app_viewpost',['id'=>$id]);
        }
        $comments=$entityManager->getRepository(Comment::class)->findBy(['post'=>$id],['id'=>'DESC']);
   

        return $this->render("post/viewpost.html.twig",[
                'post'=>$post,
                'comments'=>$comments,
                'form'=>$form->createView()
        ]);
    }
}
