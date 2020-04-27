<?php

/**
 * Check if given date (defaults now) is business in Poland
 *
 * @param string $date
 * @return boolean
 */
function is_working_day($date = 'now')
{
    if (!is_numeric($date))
        $time = strtotime($date);
    else
        $time = $date;
        
    $dayOfWeek = (int) date('w', $time);
    $year = (int) date('Y', $time);

    #sprawdzenie czy to nie weekend
    if ($dayOfWeek == 6 || $dayOfWeek == 0) {
        return false;
    }

    #lista swiat stalych
    $holiday = array('01-01', '01-06', '05-01', '05-03', '08-15', '11-01', '11-11', '12-25', '12-26');

    #dodanie listy swiat ruchomych
    #wialkanoc
    $easter = date('m-d', easter_date($year));
    $easter_time = strtotime($year . '-' . $easter);
    #poniedzialek wielkanocny
    $easterSec = date('m-d', strtotime('+1 day',  $easter_time));
    #boze cialo
    $cc = date('m-d', strtotime('+60 days', $easter_time));
    #Zesłanie Ducha Świętego
    $p = date('m-d', strtotime('+49 days', $easter_time));

    $holiday[] = $easter;
    $holiday[] = $easterSec;
    $holiday[] = $cc;
    $holiday[] = $p;

    $md = date('m-d', $time);
    if (in_array($md, $holiday)) return false;

    return true;
}