<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 */
final class Item
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
     * @ORM\Column(type="boolean")
     * @Groups("NoChildren")
     */
    private bool $checked;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ShoppingList", inversedBy="items")
     * @ORM\JoinColumn(nullable=false)
     */
    private ShoppingList $list;

    public function __construct(string $name, ShoppingList $list, bool $checked = false) {
        $this->name = $name;
        $this->checked = $checked;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getChecked(): bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked): self
    {
        $this->checked = $checked;

        return $this;
    }

    public function getList(): ShoppingList
    {
        return $this->list;
    }

    public function setList(ShoppingList $list): self
    {
        $this->list = $list;

        return $this;
    }
}
