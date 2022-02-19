<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThumbRepository")
 */
class Thumb
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Post
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="thumb")
     * @ORM\JoinColumn(nullable=false)
     */
    private $post;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $liker;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $postOwner;

    /**
     * @ORM\Column(type="datetime")
     */
    private $likedAt;

    public function __construct()
    {
        $this->likedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(Post $post): void
    {
        $this->post = $post;
    }

    public function getLiker(): ?string
    {
        return $this->liker;
    }

    public function setLiker(string $liker): self
    {
        $this->liker = $liker;
        return $this;
    }

    public function getPostOwner(): ?string
    {
        return $this->postOwner;
    }

    public function setPostOwner(string $postOwner): self
    {
        $this->postOwner = $postOwner;
        return $this;
    }

    public function getLikedAt(): ?\DateTimeInterface
    {
        return $this->likedAt;
    }

    public function setLikedAt(\DateTimeInterface $likedAt): self
    {
        $this->likedAt = $likedAt;
        return $this;
    }
}
