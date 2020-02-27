<?php

/*
 * Kod źródłowy chroniony prawem autorskim.
 * Autor Daniel Bośnjak
 * Pliki ładowane przez WPCMS udostępnione są na licencji OpenSource v3.0
 */


/* * *****
 *
 * REGULAR METHOD
 *
 */

function array_to_object($array = [])
{
    $object = new stdClass();
    foreach ($array as $key => $value) {
        $object->$key = $value;
    }
    return $object;
}

/**
 * Adds to arrays
 */
function array_add($a1, $a2)
{  
    // adds the values at identical keys together
    $aRes = $a1;
    foreach (array_slice(func_get_args(), 1) as $aRay) {
        foreach (array_intersect_key($aRay, $aRes) as $key => $val)
            $aRes[$key] += $val;
        $aRes += $aRay;
    }
    return $aRes;
}


