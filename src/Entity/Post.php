<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */
final class Post
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="text", unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=720)
     * @Assert\Regex("/^\w+/")
     */
    private $body;

    /**
     * @ORM\Column(type="datetime")
     */
    private $at;

    /**
     * @var Comment[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post", orphanRemoval=true, cascade={"persist"})
     */
    private $comment;

    /**
     * @var Comment[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Thumb", mappedBy="post", orphanRemoval=true, cascade={"persist"})
     */
    private $thumb;
    
    public function __construct()
    {
        $this->at = new \DateTime();
        $this->comment = new ArrayCollection();
        $this->thumb   = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function getAt(): ?\DateTimeInterface
    {
        return $this->at;
    }

    public function setAt(\DateTimeInterface $at): self
    {
        $this->at = $at;
        return $this;
    }
    /**
     *
     */
    public function getComment(): Collection
    {
        return $this->comment;
    }

    public function addComment(Comment $comment): void
    {
        $comment->setPost($this);
        if (!$this->comment->contains($comment)) {
            $this->comment->add($comment);
        }
    }

    public function removeComment(Comment $comment): void
    {
        $this->comment->removeElement($comment);
    }
    
    /**
     *
     */
    public function getThumb(): Collection
    {
        return $this->thumb;
    }

    public function addThumb(Thumb $thumb): void
    {
        $thumb->setPost($this);
        if (!$this->thumb->contains($thumb)) {
            $this->thumb->add($thumb);
        }
    }

    public function removeThumb(Thumb $thumb): void
    {
        $this->thumb->removeElement($thumb);
    }
}