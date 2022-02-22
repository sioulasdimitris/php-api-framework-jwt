<?php

class Helper
{
    public static function calculateTotal(string $age, string $startDate, string $endDate): float
    {
        $tripLength = self::getDateDiff($startDate, $endDate);
        $age = self::formatAge($age);
        $total = 0;

        foreach ($age as $ageValue) {
            $total += 3 * self::getAgeLoad($ageValue) * $tripLength;
        }

        return $total;
    }

    #verify a date string is ISO 8601 formatted
    public static function isDateValid($date): bool
    {
        if (preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/', $date) > 0) {
            return true;
        } else {
            return false;
        }
    }

    #verify currency
    public static function isCurrencyValid($currencyId): bool
    {
        switch ($currencyId) {
            case strcasecmp($currencyId, "EUR") == 0 || strcasecmp($currencyId, "USD") == 0 || strcasecmp($currencyId, "GBP") == 0:
                return true;
            default:
                return false;
        }
    }

    public static function isAgeValid(array $age): bool
    {
        #check if a string contains non-numeric values
        foreach ($age as $ageValue) {
            if (!ctype_digit($ageValue)) {
                return false;
            }
        }
        return true;
    }

    public static function formatAge(string $age): array
    {
        $age = str_replace(' ', '', $age); #remove all blank spaces
        $age = explode(",", $age);
        return $age;
    }

    public static function getDateDiff(string $startDate, string $endDate)
    {
        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);
        $datediff = $endDate - $startDate;
        $overalDays = (integer)round($datediff / (60 * 60 * 24) + 1);
        return $overalDays;
    }

    private static function getAgeLoad($age): float
    {
        switch ($age) {
            case $age >= 18 && $age <= 30:
                return 0.6;
            case $age >= 31 && $age <= 40:
                return 0.7;
            case $age >= 41 && $age <= 50:
                return 0.8;
            case $age >= 51 && $age <= 60:
                return 0.9;
            case $age >= 61 && $age <= 70:
                return 1;
            default:
                return 0;
        }

    }

}

