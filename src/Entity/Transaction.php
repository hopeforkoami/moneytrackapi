<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $onlineID = null;

    #[ORM\ManyToOne(inversedBy: 'offlineTransactions')]
    private ?Account $accountLocal = null;

    #[ORM\ManyToOne(inversedBy: 'onlineTransactions')]
    private ?Account $accountonline = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private ?string $amount = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $personName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $transactionDate = null;

    #[ORM\Column]
    private ?bool $synced = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deleted_at = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    private ?TypeTransaction $typeTransaction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOnlineID(): ?string
    {
        return $this->onlineID;
    }

    public function setOnlineID(?string $onlineID): static
    {
        $this->onlineID = $onlineID;

        return $this;
    }

    public function getAccountLocal(): ?Account
    {
        return $this->accountLocal;
    }

    public function setAccountLocal(?Account $accountLocal): static
    {
        $this->accountLocal = $accountLocal;

        return $this;
    }

    public function getAccountonline(): ?Account
    {
        return $this->accountonline;
    }

    public function setAccountonline(?Account $accountonline): static
    {
        $this->accountonline = $accountonline;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPersonName(): ?string
    {
        return $this->personName;
    }

    public function setPersonName(string $personName): static
    {
        $this->personName = $personName;

        return $this;
    }

    public function getTransactionDate(): ?\DateTimeInterface
    {
        return $this->transactionDate;
    }

    public function setTransactionDate(\DateTimeInterface $transactionDate): static
    {
        $this->transactionDate = $transactionDate;

        return $this;
    }

    public function isSynced(): ?bool
    {
        return $this->synced;
    }

    public function setSynced(bool $synced): static
    {
        $this->synced = $synced;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    public function getTypeTransaction(): ?TypeTransaction
    {
        return $this->typeTransaction;
    }

    public function setTypeTransaction(?TypeTransaction $typeTransaction): static
    {
        $this->typeTransaction = $typeTransaction;

        return $this;
    }
}
