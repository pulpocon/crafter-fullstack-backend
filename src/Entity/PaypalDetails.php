<?php

namespace App\Entity;

use App\Repository\PaypalDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaypalDetailsRepository::class)]
class PaypalDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 26)]
    private $reference;

    #[ORM\Column(type: 'object')]
    private $details;

    #[ORM\Column(type: 'string', length: 17)]
    private $paypalId;

    #[ORM\Column(type: 'string', length: 25)]
    private $status;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private $paid;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private $fee;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private $netAmount;

    public function __construct()
    {
        $this->fee = 0;
        $this->netAmount = 0;
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

    public function getDetails(): ?object
    {
        return $this->details;
    }

    public function setDetails(object $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getPaypalId(): ?string
    {
        return $this->paypalId;
    }

    public function setPaypalId(string $paypalId): self
    {
        $this->paypalId = $paypalId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPaid(): ?string
    {
        return $this->paid;
    }

    public function setPaid(string $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function getFee(): ?string
    {
        return $this->fee;
    }

    public function setFee(string $fee): self
    {
        $this->fee = $fee;

        return $this;
    }

    public function getNetAmount(): ?string
    {
        return $this->netAmount;
    }

    public function setNetAmount(string $netAmount): self
    {
        $this->netAmount = $netAmount;

        return $this;
    }
}
