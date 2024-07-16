<?php

namespace App\Controller;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    private $privateaccessKey = "AH_Dg2XTMvJBOsg3Uf_q5lEGp4Wtv8F00SZazX71QRY";

    private $urlphoto = "https://api.unsplash.com";
    
    private $urlMeteo = "http://my.meteoblue.com/packages";

    private $apiMeteoKey = "wlcI1Cte59OAgNRz";

    #[Route('/', name: 'home' , methods: ['GET', 'POST'])]
    public function index(Request $request)
    {
        // $client = HttpClient::create();
        // $responseApi = $client->request(
            //     'GET',
            //     "https://api.unsplash.com/photos/".
            //     "?client_id=$this->privateaccessKey".
            //     "&per_page=5"
            // );
            
            // $content = $responseApi->getContent();
            // $datas = $responseApi->toArray();
        
        $images = [];
        
        $form = $this->createFormBuilder()
        ->add("query", TextType::class, [
            'label' => 'Search',
        ])
        ->add('ask',SubmitType::class, [
            "label" => "Search",
        ])->getForm();


        $form->handleRequest($request);//recoit la requette en post
        if ($form->isSubmitted() && $form->isValid()) {
            $query = $form->get('query')->getData();
            $datas = $this->apiRequest($this ->urlphoto,
            '/search/photos', ['per_page' => 5,
            'query' => '.$query.',"client_id" => $this->privateaccessKey], 'GET');
            foreach ($datas['results'] as $data) {//parcours le tableau de l'api
                $images[] = $data['urls']['small'];
            }
            
        }

        return $this->render('home/index.html.twig',[
        'images' => $images,
        'form' => $form->createView(),
    ]);
    }

    #[Route('meteo', name: 'meteo' , methods: ['GET', 'POST'])]
    public function meteo(Request $request)
    {
        $datas = [];
        $form = $this->createFormBuilder()
        ->add("lat", TextType::class,["label"=>"latitude"]) 
        ->add('lon', TextType::class,["label"=>"longitude"])
        ->add('ask',SubmitType::class,["label"=>"Voir"])
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {//si le formulaire est posté
            $lat = $form->get('lat')->getData();
            $lon = $form->get('lon')->getData();
            $datas = $this->apiRequest($this->urlMeteo,
            '/current',
            ['lat' => $lat, 'lon' => $lon, 'apikey' => $this->apiMeteoKey],
            'GET');
            // dump($datas);
        }

        return $this->render('home/meteo.html.twig',[
        'form' => $form->createView(),
        'datas' => $datas,
    ]);
    }
    //fonction d'usage de requette API
    private function apiRequest(string $baseurl,string $methode, $parameters = [], $protocol): array
    {
        $client = HttpClient::create();

        $url = $baseurl . $methode ."?";
        // $parameters['client_id'] = $this->privateaccessKey;
        $ps = [];
        foreach ($parameters as $key => $value) {
            $ps[] = "$key=$value";
        }
        $url .= implode("&", $ps);
        $responseApi = $client->request(
            $protocol,
            $url,
        );
        return $responseApi->toArray(); //renvoie un tableau multidimentionnel
    }
}