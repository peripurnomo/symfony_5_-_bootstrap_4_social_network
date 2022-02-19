<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FollowRepository")
 */
class Follow
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\user", inversedBy="follows")
     */
    private $user;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="integer")
     */
    private $follower;

    /**
     * @ORM\Column(type="datetime")
     */
    private $followAt;

    public function __construct()
    {
        $this->followAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollower(): ?int
    {
        return $this->follower;
    }

    public function setFollower(int $follower): self
    {
        $this->follower = $follower;
        return $this;
    }

    public function getFollowAt(): ?\DateTimeInterface
    {
        return $this->followAt;
    }

    public function setFollowAt(\DateTimeInterface $followAt): self
    {
        $this->followAt = $followAt;
        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): self
    {
        $this->user = $user;
        return $this;
    }
}