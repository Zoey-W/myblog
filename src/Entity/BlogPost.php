<?php

namespace App\Entity;

use App\Repository\BlogPostRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="blog_post")
 * @ORM\Entity(repositoryClass=BlogPostRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class BlogPost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @ORM\ManyToOne(targetEntity="Author")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @ORM\Column(name="subtime", type="datetime")
     */
    private $subtime;

    /**
     * @ORM\Column(name="category", type="string", length=255)
     */
    private $category;

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor(Author $author)
    {
        $this->author = $author;

        return $this;
    }

    public function getSubtime()
    {
        return $this->subtime;
    }

    public function setSubtime($subtime)
    {
        $this->subtime = $subtime;

        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
     public function prePersist()
    {
        if (!$this->getSubtime()) {
            $this->setSubtime(new \DateTime());
        }
    }
}
