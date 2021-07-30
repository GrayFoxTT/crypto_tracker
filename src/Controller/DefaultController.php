<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\CoinMarketApi;
use App\Services\ProfitCalculator;
use App\Services\DailyProfit;
use App\Entity\Currency;
use App\Repository\CurrencyRepository;
use App\Repository\ProfitRepository;
use App\Form\AddCurrencyType;


class DefaultController extends AbstractController

{

    /**
     * @Route("/")
     */

    public function index(CoinMarketApi $coinMarket, CurrencyRepository $currencyRep, ProfitCalculator $profitCalc, DailyProfit $saveProfit)
    {

        //Connexion API
        $cryptoInfos = $coinMarket->getApiCoinMarket();
        
        if($cryptoInfos != false)
        {
            //Recherche des cyrptos en BDD
            $cryptoList = $currencyRep->findAll();

            //Valeur des cryptos

            $bitcoinValue = $cryptoInfos['data']['1']['quote']['EUR']['price'];
            $ethValue = $cryptoInfos['data']['1027']['quote']['EUR']['price'];
            $rippleValue = $cryptoInfos['data']['52']['quote']['EUR']['price'];

            //Calcul de la rentabilité

            $bitcoinProfit = round($profitCalc->dailyProfitCalc('bitcoin', $bitcoinValue));
            $ethProfit = round($profitCalc->dailyProfitCalc('ethereum', $ethValue));
            $rippleProfit = round($profitCalc->dailyProfitCalc('ripple', $rippleValue));
            
            $totalProfit = $bitcoinProfit + $ethProfit + $rippleProfit;
            
            //Sauvegarde de la rentabilité en BDD
            
            $saveProfit->dailyProfitSave($totalProfit);

            //Cours des crypto il y a 24h

            $bitcoinChange = round($cryptoInfos['data']['1']['quote']['EUR']['percent_change_24h'], 2);
            $ethChange = round($cryptoInfos['data']['1027']['quote']['EUR']['percent_change_24h'], 2);
            $rippleChange = round($cryptoInfos['data']['52']['quote']['EUR']['percent_change_24h'], 2);
            
            $cryptoMin = [
                'bitcoin' => 'BTC',
                'ethereum' => 'ETH',
                'ripple' => 'XRP',
            ];

            return $this->render('index.html.twig', [
                
                'totalProfit' => $totalProfit,
                'cryptoMin' => $cryptoMin,
                'cryptoList' => $cryptoList,
                'bitcoin' => $bitcoinChange,
                'ethereum' => $ethChange,
                'ripple' => $rippleChange,
            ]);
        }
        else
        {
            return $this->render('index.html.twig', [

                'connectionError' => 'Connexion API impossible', 

            ]);
        }


    }

    /**
     * @Route("/add", name="add")
     */

    public function add(Request $request, EntityManagerInterface $entityManager)

    {

        //Création du formulaire pour la saisie
        
        $currency = new Currency();
        $form = $this->createForm(AddCurrencyType::class, $currency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            //Ajout des données en BDD
            $entityManager->persist($currency);
            $entityManager->flush();

            $this->addFlash('success', 'Crypto ajoutée');
            return $this->redirectToRoute('add');
        }

        return $this->render('add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/remove", name="remove")
     */

    public function remove(Request $request, CurrencyRepository $deleteCrypto, EntityManagerInterface $entityManager)

    {

        //Création du formulaire pour la suppression

        $currency = new Currency();
        $form = $this->createForm(AddCurrencyType::class, $currency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $crypto = $currency->getCrypto();
            $quantity = $currency->getQuantity();
            
            //Recherche des infos saisies en BDD
            $data = $deleteCrypto->findBy([
                'crypto' => $crypto,
                'quantity' => $quantity,
            ]);

            if(!empty($data[0]))
            {
                //Suppression des données
                $entityManager->remove($data[0]);
                $entityManager->flush();

                $this->addFlash('success', 'Crypto supprimée.');
                return $this->redirectToRoute('remove');
            }
            else
            {
                $this->addFlash('success', 'Supression annulée : aucun montant correspondant.');
                return $this->redirectToRoute('remove');
            }

        }

        return $this->render('remove.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/chart", name="chart")
     */

    public function graph(ChartBuilderInterface $chartBuilder, ProfitRepository $profitRepository)

    {
        
        //Réupération de la rentabilité par jour
        $profitRepo = $profitRepository->findAll();
        
        foreach($profitRepo as $profitRep)
        {
            $labels[] = $profitRep->getProfitDay()->format('d/m/Y');
            $datas[] = $profitRep->getProfitTotal();
        }

        //Création du graphique
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Rentabilité',
                    'borderColor' => 'rgb(31, 195, 108)',
                    'data' => $datas,
                ],
            ],
        ]);

        //$chart->setOptions([/* ... */]);

        return $this->render('chart.html.twig', [
            'chart' => $chart,
        ]);
    }

}