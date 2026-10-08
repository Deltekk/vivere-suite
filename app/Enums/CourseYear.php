<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Anno frequentato (colonna core.users.course_year).
 *
 * Copre superiori (S4, S5), triennale (T), magistrale (M), fuori corso (FC),
 * laureandi e part-time (PTT = triennale part-time, PTM = magistrale part-time,
 * nel formato PTx-anno-semestre).
 */
enum CourseYear: string implements HasLabel
{
    case S4 = 'S4';
    case S5 = 'S5';
    case T1 = 'T1';
    case T2 = 'T2';
    case T3 = 'T3';
    case TFC = 'TFC';
    case M1 = 'M1';
    case M2 = 'M2';
    case MFC = 'MFC';
    case Graduating = 'Laureando';
    case PTT_1_1 = 'PTT-1-1';
    case PTT_1_2 = 'PTT-1-2';
    case PTT_2_1 = 'PTT-2-1';
    case PTT_2_2 = 'PTT-2-2';
    case PTT_3_1 = 'PTT-3-1';
    case PTT_3_2 = 'PTT-3-2';
    case PTM_1_1 = 'PTM-1-1';
    case PTM_1_2 = 'PTM-1-2';
    case PTM_2_1 = 'PTM-2-1';
    case PTM_2_2 = 'PTM-2-2';

    /**
     * Vero per gli studenti delle superiori: non hanno ancora la mail UNIPA e non hanno un corso.
     */
    public function isHighSchool(): bool
    {
        return $this === self::S4 || $this === self::S5;
    }

    /**
     * Anno previsto dopo il passaggio d'anno accademico (job AdvanceAcademicYear).
     *
     * Regole (proposta, vedi CLAUDE.md): si avanza di un anno; a fine percorso si diventa
     * fuori corso (T3 -> TFC, M2 -> MFC); fuori corso e laureandi restano dove sono; i
     * part-time avanzano di un semestre (PTT-1-1 -> PTT-1-2 -> PTT-2-1 ...). Le superiori
     * restano invariate: le conferma a mano un admin (stato "da confermare").
     * Chi nel frattempo si è laureato o ha cambiato percorso lo corregge con la conferma di ottobre.
     */
    public function next(): self
    {
        return match ($this) {
            self::T1 => self::T2,
            self::T2 => self::T3,
            self::T3 => self::TFC,
            self::M1 => self::M2,
            self::M2 => self::MFC,
            self::PTT_1_1 => self::PTT_1_2,
            self::PTT_1_2 => self::PTT_2_1,
            self::PTT_2_1 => self::PTT_2_2,
            self::PTT_2_2 => self::PTT_3_1,
            self::PTT_3_1 => self::PTT_3_2,
            self::PTT_3_2 => self::TFC,
            self::PTM_1_1 => self::PTM_1_2,
            self::PTM_1_2 => self::PTM_2_1,
            self::PTM_2_1 => self::PTM_2_2,
            self::PTM_2_2 => self::MFC,
            default => $this,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::S4 => '4ª superiore',
            self::S5 => '5ª superiore',
            self::T1 => '1° anno triennale',
            self::T2 => '2° anno triennale',
            self::T3 => '3° anno triennale',
            self::TFC => 'Triennale fuori corso',
            self::M1 => '1° anno magistrale',
            self::M2 => '2° anno magistrale',
            self::MFC => 'Magistrale fuori corso',
            self::Graduating => 'Laureando',
            default => 'Part-time '.$this->value,
        };
    }
}
