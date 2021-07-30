<?php

namespace App\Entity;

use App\Repository\ProfitRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProfitRepository::class)
 */
class Profit
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $profit_total;

    /**
     * @ORM\Column(type="date")
     */
    private $profit_day;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfitTotal(): ?int
    {
        return $this->profit_total;
    }

    public function setProfitTotal(int $profit_total): self
    {
        $this->profit_total = $profit_total;

        return $this;
    }

    public function getProfitDay(): ?\DateTimeInterface
    {
        return $this->profit_day;
    }

    public function setProfitDay(\DateTimeInterface $profit_day): self
    {
        $this->profit_day = $profit_day;

        return $this;
    }
}
