<?php

namespace Modules\Kaffettino\Enums;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Filament\Support\Contracts\HasLabel;

/**
 * Festività per le immagini "buongiornissimo kaffè!" della home di Kaffettino.
 *
 * Sono nel codice e non nel DB di proposito: nessuno può cancellarle dal pannello.
 * Per ora ci sono le festività nazionali italiane ("rosse" sul calendario), compreso San Francesco
 * (di nuovo festa nazionale dal 2026, L. 151/2025), più le ricorrenze dell'associazione note.
 * TODO: aggiungere le altre festività dell'associazione e quelle del documento (vedi CLAUDE.md).
 *
 * Il valore di ogni caso è anche il nome della cartella con le sue immagini.
 */
enum Holiday: string implements HasLabel
{
    case NewYear = 'capodanno';
    case Epiphany = 'epifania';
    case Easter = 'pasqua';
    case EasterMonday = 'pasquetta';
    case LiberationDay = 'liberazione';
    case LabourDay = 'festa-dei-lavoratori';
    case RepublicDay = 'festa-della-repubblica';
    case Assumption = 'ferragosto';
    case SaintFrancis = 'san-francesco';
    case AllSaints = 'ognissanti';
    case ImmaculateConception = 'immacolata';
    case Christmas = 'natale';
    case SaintStephen = 'santo-stefano';

    // ----- Ricorrenze dell'associazione -----
    case RosoneBirthday = 'compleanno-rosone';

    /**
     * Festività che cadono nel giorno indicato. Può essercene più di una
     * (es. nel 2038 Pasqua cade il 25 aprile, insieme alla Liberazione).
     * La data va passata nel fuso italiano (config('vivere.display_timezone')).
     *
     * @return list<self>
     */
    public static function on(CarbonInterface $date): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $holiday): bool => $holiday->dateIn($date->year)?->isSameDay($date) ?? false,
        ));
    }

    /**
     * Data della festività nell'anno indicato, o null se in quell'anno non era festa.
     */
    public function dateIn(int $year): ?CarbonImmutable
    {
        return match ($this) {
            self::NewYear => CarbonImmutable::create($year, 1, 1),
            self::Epiphany => CarbonImmutable::create($year, 1, 6),
            self::Easter => self::easterSunday($year),
            self::EasterMonday => self::easterSunday($year)->addDay(),
            self::LiberationDay => CarbonImmutable::create($year, 4, 25),
            self::LabourDay => CarbonImmutable::create($year, 5, 1),
            self::RepublicDay => CarbonImmutable::create($year, 6, 2),
            self::Assumption => CarbonImmutable::create($year, 8, 15),
            // Abolita nel 1977, ripristinata dalla L. 151/2025 a partire dal 2026
            self::SaintFrancis => $year >= 2026 ? CarbonImmutable::create($year, 10, 4) : null,
            self::AllSaints => CarbonImmutable::create($year, 11, 1),
            self::ImmaculateConception => CarbonImmutable::create($year, 12, 8),
            self::Christmas => CarbonImmutable::create($year, 12, 25),
            self::SaintStephen => CarbonImmutable::create($year, 12, 26),
            self::RosoneBirthday => CarbonImmutable::create($year, 9, 11),
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::NewYear => 'Capodanno',
            self::Epiphany => 'Epifania',
            self::Easter => 'Pasqua',
            self::EasterMonday => "Lunedì dell'Angelo",
            self::LiberationDay => 'Festa della Liberazione',
            self::LabourDay => 'Festa dei Lavoratori',
            self::RepublicDay => 'Festa della Repubblica',
            self::Assumption => 'Ferragosto',
            self::SaintFrancis => "San Francesco d'Assisi",
            self::AllSaints => 'Ognissanti',
            self::ImmaculateConception => 'Immacolata Concezione',
            self::Christmas => 'Natale',
            self::SaintStephen => 'Santo Stefano',
            self::RosoneBirthday => 'Compleanno di Rosone',
        };
    }

    /**
     * Domenica di Pasqua nel calendario gregoriano (algoritmo di Meeus/Jones/Butcher).
     * Scritto qui per non dipendere dall'estensione PHP "calendar" (easter_date()).
     */
    private static function easterSunday(int $year): CarbonImmutable
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return CarbonImmutable::create($year, $month, $day);
    }
}
