<?php
namespace Emiliomunoz\SIIChile;

class Actividad
{
    private $actividades;
    private $subrubros;
    private $rubros;

    public function __construct()
    {
        // Incluye los arreglos desde el archivo data_arrays.php
        include __DIR__ . '/../data/data_arrays.php';

        $this->actividades = $actividades;
        $this->subrubros = $subrubros;
        $this->rubros = $rubros;
    }

    public function getInfoByCodigo($codigo)
    {
        // Busca la actividad por código
        $actividad = array_filter($this->actividades, function ($item) use ($codigo) {
            return $item['codigo'] === $codigo;
        });

        if (empty($actividad)) {
            return null;
        }

        $actividad = array_shift($actividad);
        $subrubroId = $actividad['subrubro_id'];

        // Busca el subrubro asociado
        $subrubro = array_filter($this->subrubros, function ($item) use ($subrubroId) {
            return $item['id'] === $subrubroId;
        });

        if (empty($subrubro)) {
            return null;
        }

        $subrubro = array_shift($subrubro);
        $rubroId = $subrubro['rubro_id'];

        // Busca el rubro asociado
        $rubro = array_filter($this->rubros, function ($item) use ($rubroId) {
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
}
