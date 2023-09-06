<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $businessName;

    #[ORM\Column(type: 'string', length: 255)]
    private $cif;

    #[ORM\Column(type: 'string', length: 255)]
    private $address;

    #[Assert\Email(
        message: 'El correo no tiene un formato válido',
    )]
    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    #[ORM\Column(type: 'datetime_immutable')]
    private $date;

    #[ORM\Column(type: 'text', length: 65535)]
    private $document;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: Ticket::class)]
    private $tickets;

    private string $accessEmail = '';
    private string $accessReference = '';

    public function __construct()
    {
        $this->document = '';
        $this->date = new DateTimeImmutable();
        $this->tickets = new ArrayCollection();
    }

    public static function fromTicket(Ticket $ticket) : self
    {
        $object = new self();
        $object->email = $ticket->getEmailInvoice();
        $object->tickets->add($ticket);
        return $object;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBusinessName(): ?string
    {
        return $this->businessName;
    }

    public function setBusinessName(string $businessName): self
    {
        $this->businessName = $businessName;

        return $this;
    }

    public function getCif(): ?string
    {
        return $this->cif;
    }

    public function setCif(string $cif): self
    {
        $this->cif = $cif;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

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

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
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
            $ticket->setInvoice($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket) && $ticket->getInvoice() === $this) {
            $ticket->setInvoice(null);
        }

        return $this;
    }

    public function addAccessEmail(string $email) : self
    {
        $this->accessEmail = $email;
        return $this;
    }

    public function addAccessReference(string $reference) : self
    {
        $this->accessReference = $reference;
        return $this;
    }

    public function getAccessEmail() : string
    {
        return $this->accessEmail;
    }

    public function getAccessReference() : string
    {
        return $this->accessReference;
    }

    public function addDocument(string $document): self
    {
        if ($this->document !== '') {
            throw new DomainException('Invoice already exists');
        }
        $this->document = $document;
        return $this;
    }

    public function getDocument(): string
    {
        return $this->document;
    }
}
