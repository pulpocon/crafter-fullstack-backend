<?php

namespace App\Entity;

use App\Repository\PurchaseErrorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseErrorRepository::class)]
class PurchaseError
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 26, nullable: true)]
    private $reference;

    #[ORM\Column(type: 'string', length: 17, nullable: true)]
    private $paypalId;

    #[ORM\Column(type: 'string', length: 25, nullable: true)]
    private $status;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private $paid;

    #[ORM\Column(type: 'object', nullable: true)]
    private $details;

    #[ORM\Column(type: 'string', length: 255)]
    private $error;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPaypalId(): ?string
    {
        return $this->paypalId;
    }

    public function setPaypalId(?string $paypalId): self
    {
        $this->paypalId = $paypalId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPaid(): ?string
    {
        return $this->paid;
    }

    public function setPaid(?string $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function getDetails(): ?object
    {
        return $this->details;
    }

    public function setDetails(?object $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(string $error): self
    {
        $this->error = $error;

        return $this;
    }
}
