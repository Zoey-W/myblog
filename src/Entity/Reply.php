<?php

namespace App\Entity;

use App\Repository\ReplyRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table (name="reply")
 * @ORM\Entity(repositoryClass=ReplyRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Reply
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne (targetEntity="Comment")
     * @ORM\JoinColumn (name="comment_id", referencedColumnName="id")
     */
    private $comment;

    /**
     * @ORM\ManyToOne (targetEntity="Author")
     * @ORM\JoinColumn (name="author_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @ORM\Column (name="content", type="text")
     */
    private $content;

    /**
     * @ORM\Column (name="subtime", type="datetime")
     */
    private $subtime;

    public function getId()
    {
        return $this->id;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment(Comment $comment)
    {
        $this->comment = $comment;

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

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;

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

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if(!$this->getSubtime()){
            $this->setSubtime(new \DateTime());
        }
    }
}
