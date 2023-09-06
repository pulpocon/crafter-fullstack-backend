<?php

namespace App\Entity;

use App\Repository\LeadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeadRepository::class)]
class Lead
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private $sponsor;

    #[ORM\ManyToOne(targetEntity: AttendeeInfo::class, inversedBy: 'leads')]
    #[ORM\JoinColumn(nullable: false)]
    private $attendeeInfo;

    public function __construct(string $sponsor, AttendeeInfo $attendeeInfo) {
        $this->sponsor = $sponsor;
        $this->attendeeInfo = $attendeeInfo;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSponsor() : string
    {
        return $this->sponsor;
    }

    public function getAttendeeInfo(): ?AttendeeInfo
    {
        return $this->attendeeInfo;
    }

    public function setAttendeeInfo(?AttendeeInfo $attendeeInfo): self
    {
        $this->attendeeInfo = $attendeeInfo;

        return $this;
    }

}
