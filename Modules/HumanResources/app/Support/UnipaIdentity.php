<?php

namespace Modules\HumanResources\Support;

use Illuminate\Support\Str;

/**
 * Regole sull'identità UNIPA usate in registrazione e nei controlli dello staff.
 *
 * Le mail istituzionali hanno la forma nome.cognome[NN]@community.unipa.it (es.
 * marioluigi.rossi03@...): da qui derivano il controllo nome/cognome (HR 2.2.1), il formato
 * dello username e il "pattern" che riconosce la stessa persona con un'altra mail (ban, D2).
 */
final class UnipaIdentity
{
    /**
     * Formato dello username: Nome.Cognome con eventuali cifre finali (es. MarioLuigi.Rossi03).
     */
    public const USERNAME_REGEX = '/^\p{L}+\.\p{L}+\d{0,4}$/u';

    /**
     * Vero se la mail appartiene a un dominio UNIPA.
     */
    public static function isInstitutionalEmail(string $email): bool
    {
        $domain = Str::lower(Str::after($email, '@'));

        return in_array($domain, config('humanresources.institutional_domains', []), true);
    }

    /**
     * Vero se nome e cognome corrispondono alla parte locale della mail istituzionale
     * (spazi, accenti, apostrofi e cifre finali non contano).
     */
    public static function emailMatchesName(string $email, string $name, string $surname): bool
    {
        return self::emailPattern($email) === self::normalize($name).'.'.self::normalize($surname);
    }

    /**
     * Pattern che identifica la persona a prescindere dalla mail usata: parte locale in
     * minuscolo, senza cifre finali (marioluigi.rossi03@... -> "marioluigi.rossi").
     */
    public static function emailPattern(string $email): string
    {
        return (string) preg_replace('/\d+$/', '', Str::lower(Str::before($email, '@')));
    }

    /**
     * Username suggerito a partire da nome e cognome (es. "Mario Luigi", "Rossi" -> MarioLuigi.Rossi).
     */
    public static function suggestedUsername(string $name, string $surname): string
    {
        return Str::studly(self::normalize($name, keepCase: true)).'.'.Str::studly(self::normalize($surname, keepCase: true));
    }

    /**
     * Nome normalizzato per i confronti: senza accenti, spazi, apostrofi e trattini.
     */
    public static function normalize(string $value, bool $keepCase = false): string
    {
        $ascii = Str::ascii($value);
        $letters = (string) preg_replace('/[^A-Za-z]/', '', $keepCase ? ucwords(Str::lower($ascii)) : $ascii);

        return $keepCase ? $letters : Str::lower($letters);
    }

    /**
     * Mail parzialmente nascosta, da mostrare senza rivelarla per intero (es. ma***03@community.unipa.it).
     */
    public static function maskEmail(string $email): string
    {
        $local = Str::before($email, '@');
        $visible = mb_strlen($local) <= 4 ? 1 : 2;

        return mb_substr($local, 0, $visible).str_repeat('*', max(3, mb_strlen($local) - $visible * 2)).mb_substr($local, -$visible).'@'.Str::after($email, '@');
    }
}
