<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class BlogController extends AbstractController
{
    private $entityManager;
    private $authorRepository;
    private $blogPostRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->blogPostRepository = $entityManager->getRepository('App:BlogPost');
        $this->authorRepository = $entityManager->getRepository('App:Author');
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('blog/index.html.twig', [
            'blogPosts' => $this->blogPostRepository->findAll()
        ]);
    }

    /**
     * @Route ("/show/{slug}", name="show_post")
     */
    public function showPostAction($slug){
        //是否根据session来判断要展示的内容
        $blogPost = $this->blogPostRepository->findOneBySlug($slug);

        if(!$blogPost){
            $this->addFlash('error', 'Unable to find post!');
            return $this->redirectToRoute('homepage');
        }

        return $this->render('blog/show_post.html.twig',[
            'blogPost' => $blogPost
        ]);
    }

    /**
     * @Route ("/show/author/{name}", name="show_author")
     */
    public function showAuthorAction($name){
        $author = $this->authorRepository->findOneByName($name);
        $blogPosts = [];
        $blogPosts = $this->blogPostRepository->findByAuthor($author);

        if(!$author){
            $this->addFlash('error', 'Unable to find author!');
            return $this->redirectToRoute('homepage');
        }

        return $this->render('blog/show_author.html.twig',[
            'author' => $author,
            'blogPosts' => $blogPosts
        ]);
    }

}
