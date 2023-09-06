<?php

namespace App\Entity;

use App\Repository\TicketPlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketPlanRepository::class)]
#[ORM\Index(fields: ["active"], name: "active_idx")]
class TicketPlan
{
    public const AVAILABLE_ACCESSES = [
        'Track Crafter FullStack' => 'crafter-full',
        'Open Space' => 'openspace',
        'Track Crafter' => 'crafter',
        'Track Devops' => 'devops',
        'Track Data' => 'data',
        'Track Management' => 'management',
        'Charlas PulpitoCon' => 'pulpito',
        'Charlas PulpoCon' => 'charlas',
        'Ice Breaker' => 'icebreaker'
        //'Sólo Charlas Sábado' => 'charlas+comida'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $slug;

    #[ORM\Column(type: 'string', length: 255)]
    private $description;

    #[ORM\Column(type: 'decimal', precision: 7, scale: 2)]
    private $price;

    #[ORM\Column(type: 'decimal', precision: 4, scale: 2)]
    private $tax;

    #[ORM\Column(type: 'integer')]
    private $quantity;

    #[ORM\Column(type: 'integer')]
    private $fewQuantityAlert;

    #[ORM\Column(type: 'integer')]
    private $position;

    #[ORM\Column(type: 'boolean')]
    private $active;

    #[ORM\OneToMany(mappedBy: 'ticketPlan', targetEntity: Ticket::class)]
    private $tickets;

    #[ORM\Column(type: 'boolean')]
    private $visible;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $allowedEmails = [];

    #[ORM\Column(type: 'string', length: 255)]
    private $accessTo;

    #[ORM\Column(type: 'boolean')]
    private $free = false;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
        $this->fewQuantityAlert = 0;
    }

    public function __toString() : string
    {
        return $this->name;
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

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description): void
    {
        $this->description = $description;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTotalPrice() : ?float
    {
        return $this->price + $this->tax;
    }

    public function getTax() : ?float
    {
        return $this->tax;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setTicketPlan($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket) && $ticket->getTicketPlan() === $this) {
            $ticket->setTicketPlan(null);
        }

        return $this;
    }

    public function getAvailableTickets() : int
    {
        $available = $this->quantity - $this->totalTicketsNotRevoked();
        return max($available, 0);
    }

    private function totalTicketsNotRevoked() : int
    {
        return count(array_filter($this->tickets->toArray(), static function (Ticket $ticket) {
            return !$ticket->isRevoked();
        }));
    }

    public function getTicketsSold() : int
    {
        return $this->totalTicketsNotRevoked();
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getAllowedEmails(): ?array
    {
        return $this->allowedEmails;
    }

    public function setAllowedEmails(?array $allowedEmails): self
    {
        $this->allowedEmails = $allowedEmails;

        return $this;
    }

    public function setTax(string $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getFewQuantityAlert(): ?int
    {
        return $this->fewQuantityAlert;
    }

    public function setFewQuantityAlert(int $fewQuantityAlert): self
    {
        $this->fewQuantityAlert = $fewQuantityAlert;

        return $this;
    }

    public function fewQuantityAlertIsNeeded() : bool
    {
        return $this->fewQuantityAlert >= $this->getAvailableTickets();
    }

    public function removeEmailIfNeeded(string $email) : void
    {
        if (true === $this->visible) {
            return;
        }

        $emailIndex = array_search($email, $this->allowedEmails, true);
        if (false === $emailIndex) {
            return;
        }

        unset($this->allowedEmails[$emailIndex]);
    }

    public function getAccessTo(): array
    {
        if (null === $this->accessTo) {
            return [];
        }
        return explode('|', $this->accessTo);
    }

    public function setAccessTo(array $accessTo): self
    {
        $this->accessTo = implode('|', $accessTo);

        return $this;
    }

    public function isFree(): ?bool
    {
        return $this->free;
    }

    public function setFree(bool $free): self
    {
        $this->free = $free;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }
}
