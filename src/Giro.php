<?php
namespace Emiliomunoz\SIIChile;

class Giro
{
    private static $actividades;
    private static $subrubros;
    private static $rubros;

    public static function initialize()
    {
        include __DIR__ . '/../data/giros_data.php';

        self::$actividades = $actividades;
        self::$subrubros = $subrubros;
        self::$rubros = $rubros;

    }

    public static function getInfoByCodigo($codigo)
    {
        if (self::$actividades === null) {
            self::initialize();
        }


        // Busca la actividad por código
        $actividad = array_filter(self::$actividades, function ($item) use ($codigo) {
            return $item['codigo'] === $codigo;
        });

        dump($actividad);
        if (empty($actividad)) {
            return null;
        }

        $actividad = array_shift($actividad);
        $subrubroId = $actividad['subrubro_id'];

        // Busca el subrubro asociado
        $subrubro = array_filter(self::$subrubros, function ($item) use ($subrubroId) {
            return $item['id'] === $subrubroId;
        });

        if (empty($subrubro)) {
            return null;
        }

        $subrubro = array_shift($subrubro);
        $rubroId = $subrubro['rubro_id'];

        // Busca el rubro asociado
        $rubro = array_filter(self::$rubros, function ($item) use ($rubroId) {
            return $item['id'] === $rubroId;
        });

        if (empty($rubro)) {
            return null;
        }

        $rubro = array_shift($rubro);

        // Devuelve toda la información relacionada
        return [
            'actividad' => $actividad,
            'subrubro' => $subrubro,
            'rubro' => $rubro
        ];
    }

    public static function getSubRubroByActividad($codigo)
    {
        if (self::$actividades === null) {
            self::initialize();
        }

        $actividad = array_filter(self::$actividades, function ($item) use ($codigo) {
            return $item['codigo'] === $codigo;
        });

        if (empty($actividad)) {
            return null;
        }

        $actividad = array_shift($actividad);
        $subrubroId = $actividad['subrubro_id'];

        $subrubro = array_filter(self::$subrubros, function ($item) use ($subrubroId) {
            return $item['id'] === $subrubroId;
        });

        if (empty($subrubro)) {
            return null;
        }

        $subrubro = array_shift($subrubro);

        return $subrubro;
    }

    public static function getRubroById($codigo)
    {
        if (self::$rubros === null) {
            self::initialize();
        }

        $rubro = array_filter(self::$rubros, function ($item) use ($codigo) {
            return $item['id'] === $codigo;
        });

        if (empty($rubro)) {
            return null;
        }

        $rubro = array_shift($rubro);

        return $rubro;
    }


}
