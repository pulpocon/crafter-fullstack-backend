<?php

namespace App\Entity;

use App\Repository\TicketAbandonedRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TicketAbandonedRepository::class)]
class TicketAbandoned
{
    public const SHIRT_TYPES = ['hombre', 'mujer'];
    public const SHIRT_SIZES = ['xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl'];
    public const FEEDINGS = ['Omnivoro', 'Vegetariano', 'Vegano'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Your first name must be at least 2 characters long',
        maxMessage: 'Your first name cannot be longer than 50 characters',
    )]
    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Your first name must be at least 2 characters long',
        maxMessage: 'Your first name cannot be longer than 50 characters',
    )]
    #[ORM\Column(type: 'string', length: 255)]
    private $surname;

    #[Assert\Email(
        message: 'El correo no tiene un formato válido',
    )]
    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    #[ORM\Column(type: 'string', length: 10)]
    #[Assert\Choice(choices: Ticket::SHIRT_TYPES, message: 'Choose a valid type.')]
    private $shirtType;

    #[ORM\Column(type: 'string', length: 4)]
    #[Assert\Choice(choices: Ticket::SHIRT_SIZES, message: 'Choose a valid size.')]
    private $shirtSize;

    #[ORM\Column(type: 'string', length: 25)]
    #[Assert\Choice(choices: Ticket::FEEDINGS, message: 'Choose a valid feeding.')]
    private $feeding;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $allergies;

    #[ORM\ManyToOne(targetEntity: TicketPlan::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private $ticketPlan;

    #[ORM\Column(type: 'datetime_immutable')]
    private $startDate;

    #[ORM\Column(type: 'string', length: 26, unique: true)]
    private $reference;

    private function __construct() {}

    public static function fromTicket(Ticket $ticket) : self
    {
        $object = new self();
        $object->setTicketPlan($ticket->getTicketPlan())
            ->setAllergies($ticket->getAllergies())
            ->setEmail($ticket->getEmail())
            ->setFeeding($ticket->getFeeding())
            ->setName($ticket->getName())
            ->setShirtSize($ticket->getShirtSize())
            ->setShirtType($ticket->getShirtType())
            ->setStartDate($ticket->getStartDate())
            ->setSurname($ticket->getSurname());

        $object->id = $ticket->getId();
        $object->reference = $ticket->getReference();

        return $object;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getShirtType(): ?string
    {
        return $this->shirtType;
    }

    public function setShirtType(string $shirtType): self
    {
        $this->shirtType = $shirtType;

        return $this;
    }

    public function getShirtSize(): ?string
    {
        return $this->shirtSize;
    }

    public function setShirtSize(string $shirtSize): self
    {
        $this->shirtSize = $shirtSize;

        return $this;
    }

    public function getFeeding(): ?string
    {
        return $this->feeding;
    }

    public function setFeeding(string $feeding): self
    {
        $this->feeding = $feeding;

        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): self
    {
        $this->allergies = $allergies;

        return $this;
    }

    public function getTicketPlan(): ?TicketPlan
    {
        return $this->ticketPlan;
    }

    public function setTicketPlan(?TicketPlan $ticketPlan): self
    {
        $this->ticketPlan = $ticketPlan;

        return $this;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getReference() : string
    {
        return $this->reference;
    }

    public function finish() : self
    {
        $this->endDate = new DateTimeImmutable();
        return $this;
    }
}
