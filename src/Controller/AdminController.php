<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Entity\Reply;
use App\Form\PostFormType;
use App\Form\ReplyFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
        $comments = $this->commentRepository->findByBlogPost($blogPost);
        $replies = [];

        if(!$blogPost){
            $this->addFlash('error', 'Unable to find post!');
            return $this->redirectToRoute('admin_index');
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

        return $this->render('admin/show_post.html.twig',[
            'blogPost' => $blogPost,
            'comments' => $comments,
            'replies' => $replies
        ]);
    }

    /**
     * @Route ("/admin/reply-com/{comId}", name="admin_reply_com")
     */
    public function replyComAction($comId, Request $request){
        $comment = $this->commentRepository->findOneById($comId);
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUsername());

        $reply = new Reply();
        $reply->setComment($comment);
        $reply->setAuthor($author);

        if(!$comment || $author !== $comment->getBlogPost()->getAuthor()){
            $this->addFlash('error', 'Unable to reply to comment!');
            return $this->redirectToRoute('admin_index');
        }

        $form = $this->createForm(ReplyFormType::class, $reply);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->entityManager->persist($reply);
            $this->entityManager->flush();

            //$this->addFlash('success', 'You made a reply.');
            return new Response();
        }

        return $this->render('admin/reply_com.html.twig', [
            'comment' => $comment,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route ("/admin/delete-com/{comId}", name="admin_delete_com")
     */
    public function deleteComAction($comId)
    {
        $comment = $this->commentRepository->findOneById($comId);
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUsername());

        if(!$comment || $author !== $comment->getBlogPost()->getAuthor()){
            $this->addFlash('error', 'Unable to delete comment!');
            return $this->redirectToRoute('admin_index');
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        $this->addFlash('success', 'Comment was deleted!');

        return $this->redirectToRoute('admin_index');
    }
}