<?php namespace Emiliomunoz\SIIChile;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class Consulta
{
    protected $rut;
    const XPATH_RAZON_SOCIAL = '//html/body/div/div[4]';
    const XPATH_INICIO_ACTIVIDADES = '//html/body/div/span[3]';
    const XPATH_ACTIVITIES   = '//html/body/div/table[1]/tr';

    public function __construct($rut)
    {
        $this->rut = new Rut($rut);
        $this->client = HttpClient::create(['verify_peer' => false]);
    }

    public function sii()
    {
        return $this->parse($this->fetch());
    }

    private function fetch()
    {
        $captcha = $this->fetchCaptcha();
        $response = $this->client->request('POST', 'https://zeus.sii.cl/cvc_cgi/stc/getstc', [
            'body' => [
                'RUT' => $this->rut->number,
                'DV'  => $this->rut->code,
                'PRG' => 'STC',
                'OPC' => 'NOR',
                'txt_code' => $captcha[0],
                'txt_captcha' => $captcha[1]
            ]
        ]);
        return $response->getContent();
    }

    private function fetchCaptcha()
    {
        $response = $this->client->request('POST', 'https://zeus.sii.cl/cvc_cgi/stc/CViewCaptcha.cgi', [
            'body' => ['oper' => 0]
        ]);
        $json = json_decode($response->getContent(), true);
        $code = substr(base64_decode($json["txtCaptcha"]), 36, 4);
        $captcha = $json["txtCaptcha"];
        return [$code, $captcha];
    }

    private function parse($html)
    {
        $crawler = new Crawler($html);
        $razonSocial = ucwords(strtolower(trim($crawler->filterXPath(self::XPATH_RAZON_SOCIAL)->text())));
        $inicioActividades = "";

        $_inicioActividades = ucwords(strtolower(trim($crawler->filterXPath(self::XPATH_INICIO_ACTIVIDADES)->text())));
        if (preg_match("/\d{2}-\d{2}-\d{4}/", $_inicioActividades, $coincidencias)) {
            $inicioActividades = $coincidencias[0];
        }

        $actividades = [];
        $crawler->filterXPath(self::XPATH_ACTIVITIES)->each(function (Crawler $node, $i) use (&$actividades) {
            if ($i > 0) {
                $codigo = $node->filterXPath('//td[2]/font')->text();
                $subrubro = Giro::getSubRubroByActividad($codigo);
                $rubro = Giro::getRubroById($subrubro['rubro_id']);

                $actividades[] = [
                    'giro'      => $node->filterXPath('//td[1]/font')->text(),
                    'codigo'    => $codigo,
                    'categoria' => $node->filterXPath('//td[3]/font')->text(),
                    'afecta'    => $node->filterXPath('//td[4]/font')->text() == 'Si',
                    'rubro'     => $rubro['nombre'],
                    'subrubro'  => $subrubro['nombre']
                ];
            }
        });

        return ['razonSocial' => $razonSocial, 'inicioActividades' => $inicioActividades, 'actividades' => $actividades];
    }
}
