<?php

namespace App\Entity;

use App\Repository\CouponRepository;
use App\Service\Payment\Coupon\Type as CouponType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
class Coupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'smallint', enumType: CouponType::class)]
    private ?CouponType $type = null;

    #[ORM\Column(type: 'integer')]
    private ?int $value = null;

    #[ORM\Column(type: 'string', length: 20, unique: true)]
    private ?string $code;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?CouponType
    {
        return $this->type;
    }

    public function setType(CouponType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }
}
