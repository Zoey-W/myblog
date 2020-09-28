<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class BlogController extends AbstractController
{
    private $entityManager;
    private $authorRepository;
    private $blogPostRepository;
    private $commentRepository;
    private $replyRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->blogPostRepository = $entityManager->getRepository('App:BlogPost');
        $this->authorRepository = $entityManager->getRepository('App:Author');
        $this->commentRepository = $entityManager->getRepository('App:Comment');
        $this->replyRepository = $entityManager->getRepository('App:Reply');
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
     * @Route ("/{category}", name="show_category")
     */
    public function showCategoryAction($category)
    {
        $blogPosts = [];
        $blogPosts = $this->blogPostRepository->findByCategory($category);

        return $this->render('blog/show_category.html.twig', [
            'blogPosts' => $blogPosts
        ]);
    }

    /**
     * @Route ("/show/{slug}", name="show_post")
     */
    public function showPostAction($slug, Request $request){
        //是否根据session来判断要展示的内容
        $blogPost = $this->blogPostRepository->findOneBySlug($slug);
        $comments = $this->commentRepository->findByBlogPost($blogPost);
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUsername());
        $replies = [];

        $comment = new Comment();
        $comment->setBlogpost($blogPost);
        $comment->setAuthor($author);

        if(!$blogPost){
            $this->addFlash('error', 'Unable to find post!');
            return $this->redirectToRoute('homepage');
        }

        foreach ( $comments as $com){
            $reply = $this->replyRepository->findByComment($com);
            //在twig里怎么使用
            //$replies["".$com->getId()] = $reply;
            //存储空间浪费
            $replies[$com->getId()] = $reply;
            //按顺序存储回复
            //array_push($replies, $reply);
        }

        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $this->addFlash('success', 'You made a comment.');
            //return $this->redirectToRoute('show_post');
        }

        return $this->render('blog/show_post.html.twig',[
            'blogPost' => $blogPost,
            'comments' => $comments,
            'replies' => $replies,
            'form' => $form->createView()
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
