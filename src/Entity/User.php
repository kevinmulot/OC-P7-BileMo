<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "show_user",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"list"})
 * )
 *  * @Hateoas\Relation(
 *      "list",
 *      href = @Hateoas\Route(
 *          "users_list",
 *          absolute = true
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"show"})
 *      )
 * )
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "add_user",
 *          absolute = true
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"list","show"},
 *          excludeIf = "expr(is_granted('ROLE_USER'))"
 *      )
 * )
 * @Hateoas\Relation(
 *      "edit",
 *      href = @Hateoas\Route(
 *          "update_user",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"list","show"})
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "delete_user",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"list","show"})
 * )
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Serializer\Groups({"list", "show"})
     *
     * @Serializer\Expose
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min="2")
     * @Serializer\Groups({"list", "show"})
     *
     * @Serializer\Expose
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=180)
     * @Assert\NotBlank()
     * @Assert\Length(min="2")
     * @Serializer\Groups({"show"})
     *
     * @Serializer\Expose
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=180)
     * @Assert\NotBlank()
     * @Assert\Length(min="2")
     * @Serializer\Groups({"show"})
     *
     * @Serializer\Expose
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Serializer\Groups({"show"})
     *
     * @Serializer\Expose
     */
    private $email;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="users")
     *
     */
    private $client;

    /**
     *
     */
    public function getUsername(): string
    {
        return (string)$this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     * @return User
     */
    public function setFirstName($firstName): User
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     * @return User
     */
    public function setLastName($lastName): User
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return User
     */
    public function setEmail($email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     * @return User
     */
    public function setClient($client): User
    {
        $this->client = $client;
        return $this;
    }
}
