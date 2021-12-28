<?php

namespace App\Http\Libraries\Validation;

use App\Http\Libraries\Encrypter\Encrypter;
use App\Models\AccountActionType;
use App\Models\AccountOperationType;
use App\Models\User;

/**
 * Klasa umożliwiająca przeprowadzanie procesów walidacji danych
 */
class Validation
{
    /**
     * Sprawdzenie czy dana wartość jest unikatowa dla modelu User
     * 
     * @param string $field pole względem którego następuje przeszukiwanie
     * @param string $value wartość do sprawdzenia
     * 
     * @return bool
     */
    public static function checkUserUniqueness(string $field, string $value): bool {

        /** @var User $userExist */
        $userExist = User::where($field, $value)->first();

        return empty($userExist) ? true : false;
    }

    /**
     * Pobranie id typu akcji na koncie
     * 
     * @param string $name nazwa typu akcji na koncie
     * 
     * @return AccountActionType|null
     */
    public static function getAccountActionType(string $name): ?AccountActionType {

        $encrypter = new Encrypter;
        $encryptedName = $encrypter->encrypt($name, 27);

        /** @var AccountActionType $accountActionType */
        $accountActionType = AccountActionType::where('name', $encryptedName)->first();

        return $accountActionType ?? $accountActionType;
    }

    /**
     * Pobranie id typu operacji na koncie
     * 
     * @param string $name nazwa typu operacji na koncie
     * 
     * @return AccountOperationType|null
     */
    public static function getAccountOperationType(string $name): ?AccountOperationType {

        $encrypter = new Encrypter;
        $encryptedName = $encrypter->encrypt($name, 21);

        /** @var AccountOperationType $accountOperationType */
        $accountOperationType = AccountOperationType::where('name', $encryptedName)->first();

        return $accountOperationType ?? $accountOperationType;
    }

    /**
     * Sprawdzenie czy upłynął określony czas
     * 
     * @param string $timeReferencePoint punkt odniesienia, względem którego liczony jest czas
     * @param int $timeMarker wartość znacznika czasu przez jak długo jest aktywny
     * @param string $comparator jeden z symboli <, >, == lub ich kombinacja, liczone względem bieżącego czasu
     * @param string $unit jednostka w jakiej wyrażony jest $timeMarker
     * 
     * @return bool
     */
    public static function timeComparison(string $timeReferencePoint, int $timeMarker, string $comparator, string $unit = 'minutes'): bool {

        $now = date('Y-m-d H:i:s');
        $expirationDate = date('Y-m-d H:i:s', strtotime('+' . $timeMarker . ' ' . $unit, strtotime($timeReferencePoint)));

        $comparasion = false;

        switch ($comparator) {

            case '==':
                if ($now == $expirationDate) {
                    $comparasion = true;
                }
                break;

            case '>=':
                if ($now >= $expirationDate) {
                    $comparasion = true;
                }
                break;

            case '>':
                if ($now > $expirationDate) {
                    $comparasion = true;
                }
                break;

            case '<=':
                if ($now <= $expirationDate) {
                    $comparasion = true;
                }
                break;

            case '<':
                if ($now < $expirationDate) {
                    $comparasion = true;
                }
                break;
        }

        return $comparasion;
    }
}
