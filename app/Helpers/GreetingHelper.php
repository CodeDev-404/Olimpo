<?php

namespace App\Helpers;

class GreetingHelper
{
    private static $phrases = [
        'El éxito es la suma de pequeños esfuerzos repetidos día tras día.',
        'Haz hoy algo que tu yo del futuro te agradecerá.',
        'La actitud determina la altitud. ¡Arriba ese ánimo!',
        'Cada día es una nueva oportunidad para mejorar.',
        'El único modo de hacer un gran trabajo es amar lo que haces.',
        'No cuentes los días, haz que los días cuenten.',
        'Cree en ti y todo será posible.',
        'El esfuerzo de hoy es el éxito de mañana.',
        'Sonríe, hoy puede ser el mejor día de tu semana.',
        'La excelencia no es un acto, es un hábito.',
        'Tu única competencia eres tú mismo. Superate cada día.',
        'Las pequeñas acciones generan grandes resultados.',
        'Hoy es un buen día para tener un buen día.',
        'La disciplina es el puente entre metas y logros.',
        'No esperes el momento perfecto, toma el momento y hazlo perfecto.',
        'El trabajo en equipo hace que los sueños se vuelvan realidad.',
        'Sé la razón por la que alguien sonríe hoy.',
        'Tu dedicación inspira a los que te rodean.',
        'Los desafíos son lo que hacen la vida interesante.',
        'La paciencia es amarga, pero sus frutos son dulces.',
        'Hazlo con pasión o no lo hagas.',
        'El respeto al derecho ajeno es la paz.',
        'La honestidad es el primer capítulo del libro de la sabiduría.',
        'La calidad no es un acto, es un hábito.',
        'Tu mejor inversión es en ti mismo.',
        'El servicio con excelencia marca la diferencia.',
        'La humildad es la base de toda grandeza.',
        'La responsabilidad es el precio de la grandeza.',
        'Confía en el proceso, todo llega a su tiempo.',
        'La perseverancia es la virtud por la cual todas las cosas alcanzan su esplendor.',
        'Un cliente satisfecho es la mejor estrategia de negocio.',
        'Juntos podemos lograr lo imposible.',
        'La gratitud transforma lo que tenemos en suficiente.',
        'El conocimiento habla, pero la sabiduría escucha.',
        'La verdadera grandeza está en servir a los demás.',
    ];

    public static function getGreeting(int $userId): array
    {
        $hour = (int) now()->format('G');
        $isSunday = now()->isSunday();

        if ($isSunday) {
            $saludo = 'Buen domingo';
            $icon = 'heart';
        } elseif ($hour >= 6 && $hour < 12) {
            $saludo = 'Buenos días';
            $icon = 'sun';
        } elseif ($hour >= 12 && $hour < 19) {
            $saludo = 'Buenas tardes';
            $icon = 'sunset';
        } else {
            $saludo = 'Buenas noches';
            $icon = 'moon';
        }

        $seed = crc32($userId . '|' . now()->format('Y-m-d'));
        $index = abs($seed) % count(self::$phrases);
        $frase = self::$phrases[$index];

        return [
            'saludo' => $saludo,
            'icon' => $icon,
            'frase' => $frase,
        ];
    }
}
