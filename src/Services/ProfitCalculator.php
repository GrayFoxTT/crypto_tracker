<?php

//src/Services/ProfitCalcalculator

namespace App\Services;

use App\Repository\CurrencyRepository;

// Calculer la rentabilité à partir des cryptos actifs et de l'API Coin Market
class ProfitCalculator
{

    private $cr;
    private $profit;

    public function __construct(CurrencyRepository $cr, array $profit = [])
    {
        $this->cr = $cr;
        $this->profit = $profit;
    }

    public function dailyProfitCalc($cryptoName, $cryptoValue)
    {
        
        $requestRep = $this->cr->findBy([
            'crypto' => $cryptoName
        ]);
       
        
       foreach($requestRep as $value)
       {
            $this->profit[] = ($cryptoValue / $value->getQuantity()) - ($value->getPrice() / $value->getQuantity());
       }

       return array_sum($this->profit);

    }

}