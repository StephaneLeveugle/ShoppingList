<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ShoppingListRepository")
 */
final class ShoppingList
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("NoChildren")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Groups("NoChildren")
     */
    private string $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="lists")
     * @Assert\Count(min=1)
     */
    private Collection $owners;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="list", orphanRemoval=true, indexBy="id")
     */
    private Collection $items;

    public function __construct(string $name, User $owner)
    {
        $this->items = new ArrayCollection();
        $this->owners = new ArrayCollection();
        $this->addOwner($owner);
        $this->name = $name;
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

    /**
     * @return Collection|User[]
     */
    public function getOwners(): Collection
    {
        return $this->owners;
    }

    public function addOwner(User $owner): self
    {
        if (!$this->owners->contains($owner)) {
            $this->owners[] = $owner;
        }

        return $this;
    }

    public function removeOwner(User $owner): self
    {
        if ($this->owners->contains($owner)) {
            $this->owners->removeElement($owner);
        }

        return $this;
    }

    /**
     * @return Collection|Item[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param Item[] $items
     */
    public function addItems(array $items): self
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    public function addItem(Item $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setList($this);
        }

        return $this;
    }

    public function removeItem(Item $item): self
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            // set the owning side to null (unless already changed)
            if ($item->getList() === $this) {
                $item->setList(null);
            }
        }

        return $this;
    }
}