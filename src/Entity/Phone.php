<?php

namespace App\Entity;

use App\Repository\PhoneRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass=PhoneRepository::class)
 * @Serializer\ExclusionPolicy("All")
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "show_phone",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 * exclusion = @Hateoas\Exclusion(groups={"list"})
 * )
 *  * @Hateoas\Relation(
 *      "list",
 *      href = @Hateoas\Route(
 *          "phones_list",
 *          absolute = true
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"show"})
 *      )
 * )
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "add_phone",
 *          absolute = true
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"list","show"},
 *          excludeIf = "expr(not is_granted('ROLE_ADMIN'))"
 *      )
 * )
 * @Hateoas\Relation(
 *      "edit",
 *      href = @Hateoas\Route(
 *          "update_phone",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"list","show"},
 *          excludeIf = "expr(not is_granted('ROLE_ADMIN'))"
 *      )
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "delete_phone",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"list","show"},
 *          excludeIf = "expr(not is_granted('ROLE_ADMIN'))"
 *      )
 * )
 */
class Phone
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"list", "show"})
     *
     * @Serializer\Expose
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"list", "show"})
     * @Assert\NotBlank()
     * @Assert\Length(min="2", max="255")
     *
     * @Serializer\Expose
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Serializer\Groups({"show"})
     * @Serializer\Expose
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"show"})
     * @Assert\Range(min="0", max="1500")
     *
     * @Serializer\Expose
     */
    private $price;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }
}
