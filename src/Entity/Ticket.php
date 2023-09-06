<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TicketRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    public const SHIRT_TYPES = ['hombre', 'mujer'];
    public const SHIRT_SIZES = ['xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl'];
    public const FEEDINGS = ['Omnivoro', 'Vegetariano', 'Vegano'];
    private const KEY = '!6+Ln@=Jcsgh^+t2?7hueNQh4rU+sK2*2jGwC?^yhA5*gJZfAC';

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

    #[Assert\Email(
        message: 'El correo no tiene un formato válido',
    )]
    #[ORM\Column(type: 'string', length: 255)]
    private $emailInvoice;

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
    /** @var TicketPlan */
    private $ticketPlan;

    #[ORM\Column(type: 'datetime_immutable')]
    private $startDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $endDate;

    #[ORM\Column(type: 'string', length: 26, unique: true)]
    private $reference;

    /**
     * @var Ticket|null
     */
    #[ORM\OneToOne(targetEntity: self::class)]
    private $upgradedFrom;

    #[ORM\Column(type: 'boolean')]
    private $revoked;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'tickets')]
    private $invoice = null;

    #[ORM\Column(type: 'decimal', precision: 7, scale: 2, nullable: true)]
    private $price;

    #[ORM\Column(type: 'decimal', precision: 4, scale: 2, nullable: true)]
    private $tax;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $registered;

    public function __construct()
    {
        $this->reference = (new Ulid())->toBase32();
        $this->startDate = new DateTimeImmutable();
        $this->revoked = false;
    }

    public function __toString() : string
    {
        return $this->reference;
    }

    public static function fromPlan(?TicketPlan $ticketPlan) : self
    {
        $ticket = new Ticket();
        $ticket->setTicketPlan($ticketPlan);
        return $ticket;
    }

    public static function fromTicket(Ticket $origin) : self
    {
        $ticket = new self();
        $ticket->name = $origin->getName();
        $ticket->surname = $origin->getSurname();
        $ticket->email = $origin->getEmail();
        $ticket->emailInvoice = $origin->getEmailInvoice();
        $ticket->shirtType = $origin->getShirtType();
        $ticket->shirtSize = $origin->getShirtSize();
        $ticket->feeding = $origin->getFeeding();
        $ticket->allergies = $origin->getAllergies();
        $ticket->upgradedFrom = $origin;
        return $ticket;
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

    public function getEmailInvoice(): ?string
    {
        return $this->emailInvoice;
    }

    public function setEmailInvoice(string $email): self
    {
        $this->emailInvoice = $email;

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

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getReference() : string
    {
        return $this->reference;
    }

    public function finish() : self
    {
        $this->endDate = new DateTimeImmutable();
        $this->ticketPlan->removeEmailIfNeeded($this->email);

        $this->price = $this->ticketPlan->getPrice();
        $this->tax = $this->ticketPlan->getTax();

        $this->revokeParentIfNeeded();
        return $this;
    }

    public function getPrice(): ?float
    {
        return (float) $this->price;
    }

    public function getTax(): ?float
    {
        return (float) $this->tax;
    }

    private function revokeParentIfNeeded() : void
    {
        if (!$this->isUpgraded()) {
            return;
        }

        $this->upgradedFrom->revoked = true;
    }

    public function getHash() : string
    {
        return hash('sha256', $this->reference . self::KEY);
    }

    public function hashIsValid(string $hashToValidate) : bool
    {
        return $hashToValidate === $this->getHash();
    }

    public function isFinished() : bool
    {
        return $this->endDate instanceof DateTimeImmutable;
    }

    public function getTicketPlanName() : string
    {
        if ($this->ticketPlan === null) {
            return '';
        }
        return $this->ticketPlan->getName();
    }

    public function isUpgraded() : bool
    {
        return null !== $this->upgradedFrom;
    }

    public function setUpgradedFrom(?self $upgradedFrom) : self
    {
        $this->upgradedFrom = $upgradedFrom;
        return $this;
    }

    public function getUpgradedFrom() : ?self
    {
        return $this->upgradedFrom;
    }

    public function reconcile() : self
    {
        if (!$this->isUpgraded()) {
            return $this;
        }

        $this->name = $this->upgradedFrom->getName();
        $this->surname = $this->upgradedFrom->getSurname();
        $this->email = $this->upgradedFrom->getEmail();
        $this->emailInvoice = $this->upgradedFrom->getEmailInvoice();

        return $this;
    }

    public function isRevoked(): ?bool
    {
        return $this->revoked;
    }

    public function setRevoked(bool $revoked): self
    {
        $this->revoked = $revoked;

        return $this;
    }

    public function isFullyPaid(string $value) : bool
    {
        return $value !== $this->toPay();
    }

    private function toPay() : string
    {
        if (!$this->isUpgraded() || null === $this->upgradedFrom->getTicketPlan()) {
            return number_format($this->ticketPlan->getTotalPrice(), 2);
        }

        return number_format(
            $this->ticketPlan->getTotalPrice() - $this->upgradedFrom->getTicketPlan()->getTotalPrice(),
            2
        );
    }

    public function canAccess() : bool
    {
        return !$this->revoked;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        if ($this->invoice !== null) {
            throw new \DomainException("feo");
        }
        $this->invoice = $invoice;

        return $this;
    }

    public function registered() : self
    {
        $this->registered = new DateTimeImmutable();
        return $this;
    }

    public function wasAttendee(): bool
    {
        return $this->registered !== null;
    }
}
