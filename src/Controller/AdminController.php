<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Form\PostFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Author;
use App\Form\AuthorFormType;

class AdminController extends AbstractController
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
     * @Route("/admin/author/create", name="author_create")
     */
    public function createAuthorAction(Request $request)
    {
        if ($this->authorRepository->findOneByUsername($this->getUser()->getUserName())) {
            
            //$this->addFlash('error', 'Unable to create author, author already exists!');
            $request->getSession()->set('user_is_author', true);
            return $this->redirectToRoute('admin_index');
        }

        $author = new Author();
        $author->setUsername($this->getUser()->getUserName());

        $form = $this->createForm(AuthorFormType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($author);
            $this->entityManager->flush();

            $request->getSession()->set('user_is_author', true);
            $this->addFlash('success', 'Congratulations! You are now an author.');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('admin/create_author.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin", name="admin_index")
     */
    public function indexAction()
    {
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUsername());
        $blogPosts = [];


        $blogPosts = $this->blogPostRepository->findByAuthor($author);


        return $this->render('admin/index.html.twig',['blogPosts' => $blogPosts]);
    }

    /**
     * @Route ("/admin/create-post", name="admin_create_post")
     */
    public function createPostAction(Request $request)
    {
        $blogPost = new BlogPost();
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUsername());
        $blogPost->setAuthor($author);

        $form = $this->createForm(PostFormType::class, $blogPost);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->entityManager->persist($blogPost);
            $this->entityManager->flush();

            $this->addFlash('success', 'Congratulations! Your post is created');
            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/create_post.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route ("/admin/delete-post/{postId}", name="admin_delete_post")
     */
    public function deletePostAction($postId)
    {
        $blogPost = $this->blogPostRepository->findOneById($postId);
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUsername());

        if(!$blogPost || $author !== $blogPost->getAuthor()){
            $this->addFlash('error', 'Unable to delete post!');
            return $this->redirectToRoute('admin_index');
        }

        $this->entityManager->remove($blogPost);
        $this->entityManager->flush();

        $this->addFlash('success', 'Post was deleted!');

        return $this->redirectToRoute('admin_index');
    }

    /**
     * @Route ("/admin/edit-post/{slug}", name="admin_edit_post")
     */
    public function editPostAction($slug, Request $request)
    {
        $blogPost = $this->blogPostRepository->findOneBySlug($slug);
        $form = $this->createForm(PostFormType::class, $blogPost);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->entityManager->flush();

            $this->addFlash('success', 'Changes have been saved!');
            return $this->redirectToRoute('admin_index');
        }
        return $this->render('admin/edit_post.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route ("/admin/show/{slug}", name="admin_show_post")
     */
    //这是用户查看自己写的博客，会有删除、回复评论等功能
    public function showPostAction($slug)
    {
        $blogPost = $this->blogPostRepository->findOneBySlug($slug);

        if(!$blogPost){
            $this->addFlash('error', 'Unable to find post!');
            return $this->redirectToRoute('admin_index');
        }
        return $this->render('admin/show_post.html.twig',[
            'blogPost' => $blogPost
        ]);
    }
}