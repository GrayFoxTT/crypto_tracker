<?php

//src/Services/DailyProfit

namespace App\Services;

use App\Repository\ProfitRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Profit;
use DateTime;


// Sauvegarder une fois par jour la rentabilité quotidienne

class DailyProfit
{

    private $pr;
    private $em;

    public function __construct(ProfitRepository $pr, EntityManagerInterface $em)
    {
        $this->pr = $pr;
        $this->em = $em;
    }

    public function dailyProfitSave($profit)
    {
        $dailyProfit = new Profit();
        $today = new \DateTime();

        $requestRep = $this->pr->findBy([
            'profit_day' => $today,
        ]);

        if(empty($requestRep[0]))
        {
            $dailyProfit->setProfitTotal($profit);
            $dailyProfit->setProfitDay($today);
            $this->em->persist($dailyProfit);
            $this->em->flush();
        }
    
    }

}