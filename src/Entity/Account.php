<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $onlineID = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private ?string $balance = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $synced = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $create_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deleted_at = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emplacement = null;

    #[ORM\OneToMany(mappedBy: 'accountLocal', targetEntity: Transaction::class)]
    private Collection $offlineTransactions;

    #[ORM\OneToMany(mappedBy: 'accountonline', targetEntity: Transaction::class)]
    private Collection $onlineTransactions;

    public function __construct()
    {
        $this->offlineTransactions = new ArrayCollection();
        $this->onlineTransactions = new ArrayCollection();
    }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTimeImmutable $create_at): static
    {
        $this->create_at = $create_at;

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

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): static
    {
        $this->contact = $contact;

        return $this;
    }

    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    public function setEmplacement(?string $emplacement): static
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getOfflineTransactions(): Collection
    {
        return $this->offlineTransactions;
    }

    public function addOfflineTransaction(Transaction $offlineTransaction): static
    {
        if (!$this->offlineTransactions->contains($offlineTransaction)) {
            $this->offlineTransactions->add($offlineTransaction);
            $offlineTransaction->setAccountLocal($this);
        }

        return $this;
    }

    public function removeOfflineTransaction(Transaction $offlineTransaction): static
    {
        if ($this->offlineTransactions->removeElement($offlineTransaction)) {
            // set the owning side to null (unless already changed)
            if ($offlineTransaction->getAccountLocal() === $this) {
                $offlineTransaction->setAccountLocal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getOnlineTransactions(): Collection
    {
        return $this->onlineTransactions;
    }

    public function addOnlineTransaction(Transaction $onlineTransaction): static
    {
        if (!$this->onlineTransactions->contains($onlineTransaction)) {
            $this->onlineTransactions->add($onlineTransaction);
            $onlineTransaction->setAccountonline($this);
        }

        return $this;
    }

    public function removeOnlineTransaction(Transaction $onlineTransaction): static
    {
        if ($this->onlineTransactions->removeElement($onlineTransaction)) {
            // set the owning side to null (unless already changed)
            if ($onlineTransaction->getAccountonline() === $this) {
                $onlineTransaction->setAccountonline(null);
            }
        }

        return $this;
    }
}
