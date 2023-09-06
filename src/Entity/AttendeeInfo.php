<?php

namespace App\Entity;

use App\Repository\AttendeeInfoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttendeeInfoRepository::class)]
class AttendeeInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 26)]
    private $reference;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $position;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $years;

    #[ORM\Column(type: 'array', nullable: true)]
    private $workPreference = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $city;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $state;

    #[ORM\Column(type: 'array', nullable: true)]
    private $stack = [];

    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    #[ORM\OneToMany(mappedBy: 'attendeeInfo', targetEntity: Lead::class, orphanRemoval: true)]
    private $leads;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $surname;

    #[ORM\Column(type: 'string', length: 255)]
    private $dni;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $workStatus;

    private string $ticketEmail = '';
    private string $ticketReference = '';

    public function __construct()
    {
        $this->leads = new ArrayCollection();
    }

    public static function fromTicket(Ticket $ticket) : self
    {
        $object = new self();
        $object->setReference($ticket->getReference());
        $object->setEmail($ticket->getEmail());
        $object->setName($ticket->getName());
        $object->setSurname($ticket->getSurname());
        return $object;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getYears(): ?int
    {
        return $this->years;
    }

    public function setYears(?int $years): self
    {
        $this->years = $years;

        return $this;
    }

    public function getWorkPreference(): ?array
    {
        return $this->workPreference;
    }

    public function setWorkPreference(?array $workPreference): self
    {
        $this->workPreference = $workPreference;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getStack(): ?array
    {
        return $this->stack;
    }

    public function setStack(?array $stack): self
    {
        $this->stack = $stack;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Lead>
     */
    public function getLeads(): Collection
    {
        return $this->leads;
    }

    public function addLead(Lead $lead): self
    {
        if (!$this->leads->contains($lead)) {
            $this->leads[] = $lead;
            $lead->setAttendeeInfo($this);
        }

        return $this;
    }

    public function removeLead(Lead $lead): self
    {
        if ($this->leads->removeElement($lead) && $lead->getAttendeeInfo() === $this) {
            $lead->setAttendeeInfo(null);
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(string $dni): self
    {
        $this->dni = $dni;

        return $this;
    }

    public function getWorkStatus(): ?string
    {
        return $this->workStatus;
    }

    public function setWorkStatus(string $workStatus): self
    {
        $this->workStatus = $workStatus;

        return $this;
    }

    public function setTicketEmail(string $ticketEmail): self
    {
        $this->ticketEmail = $ticketEmail;
        return $this;
    }

    public function getTicketEmail(): ?string
    {
        return $this->ticketEmail;
    }

    public function setTicketReference(string $ticketEmail): self
    {
        $this->ticketReference = $ticketEmail;
        return $this;
    }

    public function getTicketReference(): ?string
    {
        return $this->ticketReference;
    }
}
