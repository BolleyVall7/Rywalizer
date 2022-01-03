<?php

namespace App\Models;

use App\Exceptions\ApiException;
use App\Http\ErrorCodes\AuthErrorCode;
use App\Http\ErrorCodes\BaseErrorCode;
use App\Http\Libraries\Encrypter\Encrypter;
use App\Http\Libraries\Validation\Validation;
use App\Http\Permissions\RolePermission;
use App\Http\Responses\JsonResponse;
use App\Http\Traits\Encryptable;
use App\Mail\AccountRestoration as MailAccountRestoration;
use App\Mail\PasswordReset as MailPasswordReset;
use App\Mail\EmailVerification as MailEmailVerification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;
use Mehradsadeghi\FilterQueryString\FilterQueryString;

class User extends Authenticatable implements MustVerifyEmail
{
    use Encryptable, FilterQueryString, HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'telephone',
        'password',
        'birth_date',
        'gender_id',
        'city_id',
        'address_coordinates',
        'facebook_profile',
        'instagram_profile',
        'website'
    ];

    protected $guarded = [
        'id',
        'role_id',
        'email_verified_at',
        'telephone_verified_at',
        'last_time_name_changed',
        'last_time_password_changed',
        'verified_at',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'id',
        'first_name',
        'last_name',
        'email',
        'telephone',
        'password',
        'birth_date',
        'gender_id',
        'role_id',
        'city_id',
        'address_coordinates',
        'facebook_profile',
        'instagram_profile',
        'website',
        'email_verified_at',
        'telephone_verified_at',
        'last_time_name_changed',
        'last_time_password_changed',
        'verified_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'email_verified_at' => 'string',
        'telephone_verified_at' => 'string',
        'last_time_name_changed' => 'string',
        'last_time_password_changed' => 'string',
        'verified' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string'
    ];

    protected $encryptable = [
        'first_name' => 30,
        'last_name' => 30,
        'email' => 254,
        'telephone' => 24,
        'birth_date' => 10,
        'address_coordinates' => 21,
        'facebook_profile' => 255,
        'instagram_profile' => 255,
        'website' => 255
    ];

    public function gender() {
        return $this->belongsTo(DefaultType::class, 'id');
    }

    public function role() {
        return $this->belongsTo(DefaultType::class, 'id');
    }

    public function city() {
        return $this->belongsTo(Area::class, 'id');
    }

    public function defaultTypeNameCreator() {
        return $this->hasMany(DefaultTypeName::class, 'creator_id');
    }

    public function defaultTypeNameEditor() {
        return $this->hasMany(DefaultTypeName::class, 'editor_id');
    }

    public function FriendRequestingUser() {
        return $this->hasMany(Friend::class, 'requesting_user_id');
    }

    public function FriendRespondingUser() {
        return $this->hasMany(Friend::class, 'responding_user_id');
    }

    public function iconCreator() {
        return $this->hasMany(Icon::class, 'creator_id');
    }

    public function iconEditor() {
        return $this->hasMany(Icon::class, 'editor_id');
    }

    public function imageCreator() {
        return $this->hasMany(Image::class, 'creator_id');
    }

    public function imageSupervisor() {
        return $this->hasMany(Image::class, 'supervisor_id');
    }

    public function userSetting() {
        return $this->hasOne(UserSetting::class);
    }

    public function defaultTypeCreator() {
        return $this->hasMany(DefaultType::class, 'creator_id');
    }

    public function defaultTypeEditor() {
        return $this->hasMany(DefaultType::class, 'editor_id');
    }

    public function imageAssignmentCreator() {
        return $this->hasMany(ImageAssignment::class, 'creator_id');
    }

    public function imageAssignmentEditor() {
        return $this->hasMany(ImageAssignment::class, 'editor_id');
    }

    public function accountActionCreator() {
        return $this->hasMany(AccountAction::class, 'creator_id');
    }

    public function accountActionEditor() {
        return $this->hasMany(AccountAction::class, 'editor_id');
    }

    public function accountOperationCreator() {
        return $this->hasMany(AccountOperation::class, 'creator_id');
    }

    public function accountOperationEditor() {
        return $this->hasMany(AccountOperation::class, 'editor_id');
    }

    public function areaCreator() {
        return $this->hasMany(Area::class, 'creator_id');
    }

    public function areaEditor() {
        return $this->hasMany(Area::class, 'editor_id');
    }

    public function areaSupervisor() {
        return $this->hasMany(Area::class, 'supervisor_id');
    }

    public function authentication() {
        return $this->hasMany(Authentication::class);
    }

    public function externalAuthentication() {
        return $this->hasMany(ExternalAuthentication::class);
    }

    public function rolePermissionCreator() {
        return $this->hasMany(RolePermission::class, 'creator_id');
    }

    public function commissionCreator() {
        return $this->hasMany(Commission::class, 'creator_id');
    }

    public function commissionEditor() {
        return $this->hasMany(Commission::class, 'editor_id');
    }

    public function partner() {
        return $this->hasMany(Partner::class);
    }

    public function partnerCreator() {
        return $this->hasMany(Partner::class, 'creator_id');
    }

    public function partnerEditor() {
        return $this->hasMany(Partner::class, 'editor_id');
    }

    public function partnerSettingCreator() {
        return $this->hasMany(PartnerSetting::class, 'creator_id');
    }

    public function partnerSettingEditor() {
        return $this->hasMany(PartnerSetting::class, 'editor_id');
    }

    public function discountCodeCreator() {
        return $this->hasMany(DiscountCode::class, 'creator_id');
    }

    public function discountCodeEditor() {
        return $this->hasMany(DiscountCode::class, 'editor_id');
    }

    public function discountCreator() {
        return $this->hasMany(Discount::class, 'creator_id');
    }

    public function facilityCreator() {
        return $this->hasMany(Facility::class, 'creator_id');
    }

    public function facilityEditor() {
        return $this->hasMany(Facility::class, 'editor_id');
    }

    public function facilitySupervisor() {
        return $this->hasMany(Facility::class, 'supervisor_id');
    }

    public function facilityAvailableSportCreator() {
        return $this->hasMany(FacilityAvailableSport::class, 'creator_id');
    }

    public function facilityAvailableSportEditor() {
        return $this->hasMany(FacilityAvailableSport::class, 'editor_id');
    }

    public function facilityAvailableSportSupervisor() {
        return $this->hasMany(FacilityAvailableSport::class, 'supervisor_id');
    }

    public function facilityEquipmentCreator() {
        return $this->hasMany(FacilityEquipment::class, 'creator_id');
    }

    public function facilityEquipmentEditor() {
        return $this->hasMany(FacilityEquipment::class, 'editor_id');
    }

    public function facilityEquipmentSupervisor() {
        return $this->hasMany(FacilityEquipment::class, 'supervisor_id');
    }

    public function facilityOpeningHourCreator() {
        return $this->hasMany(FacilityOpeningHour::class, 'creator_id');
    }

    public function facilityOpeningHourEditor() {
        return $this->hasMany(FacilityOpeningHour::class, 'editor_id');
    }

    public function facilityOpeningHourSupervisor() {
        return $this->hasMany(FacilityOpeningHour::class, 'supervisor_id');
    }

    public function facilityPlaceCreator() {
        return $this->hasMany(FacilityPlace::class, 'creator_id');
    }

    public function facilityPlaceEditor() {
        return $this->hasMany(FacilityPlace::class, 'editor_id');
    }

    public function facilitySpecialOpeningHourCreator() {
        return $this->hasMany(FacilitySpecialOpeningHour::class, 'creator_id');
    }

    public function facilitySpecialOpeningHourEditor() {
        return $this->hasMany(FacilitySpecialOpeningHour::class, 'editor_id');
    }

    public function facilitySpecialOpeningHourSupervisor() {
        return $this->hasMany(FacilitySpecialOpeningHour::class, 'supervisor_id');
    }

    public function facilityPlaceBooking() {
        return $this->hasMany(FacilityPlaceBooking::class);
    }

    public function minimumSkillLevelCreator() {
        return $this->hasMany(MinimumSkillLevel::class, 'creator_id');
    }

    public function minimumSkillLevelEditor() {
        return $this->hasMany(MinimumSkillLevel::class, 'editor_id');
    }

    public function minimumSkillLevelSupervisor() {
        return $this->hasMany(MinimumSkillLevel::class, 'supervisor_id');
    }

    public function sportsPositionCreator() {
        return $this->hasMany(SportsPosition::class, 'creator_id');
    }

    public function sportsPositionEditor() {
        return $this->hasMany(SportsPosition::class, 'editor_id');
    }

    public function sportsPositionSupervisor() {
        return $this->hasMany(SportsPosition::class, 'supervisor_id');
    }

    public function announcementCreator() {
        return $this->hasMany(Announcement::class, 'creator_id');
    }

    public function announcementEditor() {
        return $this->hasMany(Announcement::class, 'editor_id');
    }

    public function announcementPaymentCreator() {
        return $this->hasMany(AnnouncementPayment::class, 'creator_id');
    }

    public function announcementPaymentEditor() {
        return $this->hasMany(AnnouncementPayment::class, 'editor_id');
    }

    public function announcementSeatCreator() {
        return $this->hasMany(AnnouncementSeat::class, 'creator_id');
    }

    public function announcementSeatEditor() {
        return $this->hasMany(AnnouncementSeat::class, 'editor_id');
    }

    public function announcementParticipant() {
        return $this->hasMany(AnnouncementParticipant::class);
    }

    public function agreementCreator() {
        return $this->hasMany(Agreement::class, 'creator_id');
    }

    public function agreementEditor() {
        return $this->hasMany(Agreement::class, 'editor_id');
    }

    public function userAgreement() {
        return $this->hasMany(UserAgreement::class);
    }

    public function report() {
        return $this->hasMany(Report::class);
    }

    public function reportSupervisor() {
        return $this->hasMany(Report::class, 'supervisor_id');
    }

    /**
     * Zwrócenie podstawowych informacji o użytkowniku
     * 
     * @return array
     */
    public function basicInformation(): array {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'avatar' => $this->avatar,
            'gender_types' => $this->genderType()->first(['description', 'icon']) ?? null
        ];
    }

    /**
     * Zwrócenie prywatnych informacji o użytkowniku
     * 
     * @return array
     */
    public function privateInformation(): array {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'birth_date' => $this->birth_date,
            'address_coordinates' => $this->address_coordinates,
            'telephone' => $this->telephone,
            'facebook_profile' => $this->facebook_profile,
            'instagram_profile' => $this->instagram_profile,
            'gender_types' => $this->genderType()->first(['description', 'icon']) ?? null,
            'role_types' => $this->roleType()->first(['name', 'access_level']),
            'last_time_name_changed' => $this->last_time_name_changed,
            'last_time_password_changed' => $this->last_time_password_changed
        ];
    }

    /**
     * Zwrócenie szczegółowych informacji o użytkowniku
     * 
     * @return array
     */
    public function detailedInformation(): array {

        $accountDeleted = null;
        $accountBlocked = null;

        $accountAction = $this->accountAction()->get();

        foreach ($accountAction as $aA) {
            if (strpos($aA->accountActionType->name, 'ACCOUNT_DELETED') !== false) {
                $accountDeleted = [
                    'description' => $aA->accountActionType->description,
                    'created_at' => $aA->created_at,
                    'expires_at' => $aA->expires_at
                ];
            } else if (strpos($aA->accountActionType->name, 'ACCOUNT_BLOCKED') !== false) {
                $accountBlocked = [
                    'description' => $aA->accountActionType->description,
                    'founder' => $aA->founder->basicInformation(),
                    'created_at' => $aA->created_at,
                    'expires_at' => $aA->expires_at
                ];
            }
        }

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'birth_date' => $this->birth_date,
            'address_coordinates' => $this->address_coordinates,
            'telephone' => $this->telephone,
            'facebook_profile' => $this->facebook_profile,
            'instagram_profile' => $this->instagram_profile,
            'gender_types' => $this->genderType()->first(['description', 'icon']) ?? null,
            'role_types' => $this->roleType()->first('name'),
            'standard_login' => $this->password ? true : false,
            'external_authentiaction' => $this->externalAuthentication()->get(),
            'is_email_verified' => (bool) $this->email_verified_at,
            'account_deleted' => $accountDeleted,
            'account_blocked' => $accountBlocked,
            'last_time_name_changed' => $this->last_time_name_changed,
            'last_time_password_changed' => $this->last_time_password_changed,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    /**
     * Utworzenie rekordu do resetu hasła i wysłanie maila z tokenem
     * 
     * @return void
     */
    public function forgotPassword(): void {
        $this->prepareEmail('PASSWORD_RESET', 'reset-password', MailPasswordReset::class);
        JsonResponse::sendSuccess();
    }

    /**
     * Zresetowanie hasła użytkownika
     * 
     * @param Request $request
     * @param AccountOperation $accountOperation
     * 
     * @return void
     */
    public function resetPassword(Request $request, AccountOperation $accountOperation): void {

        if (Validation::timeComparison($accountOperation->updated_at, env('EMAIL_TOKEN_LIFETIME'), '>')) {
            throw new ApiException(AuthErrorCode::PASSWORD_RESET_TOKEN_HAS_EXPIRED());
        }

        $accountOperation->delete();

        $this->update([
            'password' => $request->password,
            'last_time_password_changed' => now()
        ]);

        if (!$request->do_not_logout) {
            PersonalAccessToken::where('tokenable_id', $this->id)->delete();
        }

        JsonResponse::sendSuccess();
    }

    /**
     * Utworzenie rekordu do weryfikacji maila oraz wysłanie maila z tokenem
     * 
     * @param bool $afterRegistartion flaga z informacją czy wywołanie metody jest pochodną procesu rejestracji nowego użytkownika
     * @param bool $ignorePause flaga określająca czy ma być sprawdzany czas ostatniego wysłania maila
     * 
     * @return void
     */
    public function sendVerificationEmail(bool $afterRegistartion = false, bool $ignorePause = false): void {

        if (!$this->email) {
            throw new ApiException(AuthErrorCode::EMPTY_EMAIL());
        }

        $accountOperationType = Validation::getAccountOperationType('EMAIL_VERIFICATION');

        if (!$accountOperationType) {
            throw new ApiException(
                BaseErrorCode::INTERNAL_SERVER_ERROR(),
                'Invalid account operation type.'
            );
        }

        $emailSendingCounter = 1;

        if (!$afterRegistartion) {

            if ($this->hasVerifiedEmail()) {
                throw new ApiException(AuthErrorCode::EMAIL_ALREADY_VERIFIED());
            }

            /** @var AccountOperation $emailVerification */
            $emailVerification = $this->accountOperation()->where('account_operation_type_id', $accountOperationType->id)->first();

            if ($emailVerification) {
                $emailSendingCounter += $emailVerification->countMailing($ignorePause);
            }
        }

        $encrypter = new Encrypter;
        $token = $encrypter->generateToken(64, AccountOperation::class);

        $this->accountOperation()->updateOrCreate([],
        [
            'account_operation_type_id' => $accountOperationType->id,
            'token' => $token,
            'email_sending_counter' => $emailSendingCounter
        ]);

        $url = env('APP_URL') . '/email/verify?token=' . $token; // TODO Poprawić na prawidłowy URL
        Mail::to($this)->send(new MailEmailVerification($url, $afterRegistartion));

        if (!$afterRegistartion) {
            JsonResponse::sendSuccess();
        }
    }

    /**
     * Zweryfikowanie adresu email użytkownika
     * 
     * @param Request $request
     * 
     * @return void
     */
    public function verifyEmail(Request $request): void {

        if ($this->hasVerifiedEmail()) {
            throw new ApiException(AuthErrorCode::EMAIL_ALREADY_VERIFIED());
        }

        $accountOperationType = Validation::getAccountOperationType('EMAIL_VERIFICATION');

        if (!$accountOperationType) {
            throw new ApiException(
                BaseErrorCode::INTERNAL_SERVER_ERROR(),
                'Invalid account operation type.'
            );
        }

        /** @var AccountOperation $emailVerification */
        $emailVerification = $this->accountOperation()->where([
            'account_operation_type_id' => $accountOperationType->id,
            'token' => $request->token
        ])->first();

        if (!$emailVerification) {
            throw new ApiException(AuthErrorCode::INVALID_EMAIL_VERIFIFICATION_TOKEN());
        }

        if (Validation::timeComparison($emailVerification->updated_at, env('EMAIL_TOKEN_LIFETIME'), '>')) {
            throw new ApiException(AuthErrorCode::EMAIL_VERIFIFICATION_TOKEN_HAS_EXPIRED());
        }

        $emailVerification->delete();

        $this->timestamps = false;
        $this->markEmailAsVerified();
    }

    /**
     * Przywrócenie usuniętego konta
     * 
     * @param AccountOperation $accountOperation
     * 
     * @return void
     */
    public function restoreAccount(AccountOperation $accountOperation): void {

        if (Validation::timeComparison($accountOperation->updated_at, env('EMAIL_TOKEN_LIFETIME'), '>')) {
            throw new ApiException(AuthErrorCode::RESTORE_ACCOUNT_TOKEN_HAS_EXPIRED());
        }

        $accountActionType = Validation::getAccountActionType('ACCOUNT_DELETED');

        if (!$accountActionType) {
            throw new ApiException(
                BaseErrorCode::INTERNAL_SERVER_ERROR(),
                'Invalid account operation type.'
            );
        }

        /** @var AccountAction $accountDeleted */
        $accountDeleted = $this->accountAction()->where([
            'user_id' => $accountOperation->user_id,
            'account_action_type_id' => $accountActionType->id
        ])->first();

        $accountOperation->delete();
        $accountDeleted->delete();

        JsonResponse::sendSuccess();
    }

    /**
     * Zaktualizowanie informacji o użytkowniku
     * 
     * @param Request $request
     * 
     * @return bool
     */
    public function updateInformation(Request $request): bool {

        $encrypter = new Encrypter;

        if ($request->email) {
            $email = $encrypter->decrypt($request->email);
            $request->merge(['email' => $email]);
        }

        if ($request->telephone) {
            $telephone = $encrypter->decrypt($request->telephone);
            $request->merge(['telephone' => $telephone]);
        }

        if ($request->facebook_profile) {
            $facebookProfile = $encrypter->decrypt($request->facebook_profile);
            $request->merge(['facebook_profile' => $facebookProfile]);
        }

        if ($request->instagram_profile) {
            $instagramProfile = $encrypter->decrypt($request->instagram_profile);
            $request->merge(['instagram_profile' => $instagramProfile]);
        }

        $updatedInformation = null;

        $isFirstName = $request->first_name && $request->first_name != $this->first_name;
        $isLastName = $request->last_name && $request->last_name != $this->last_name;
        $isEmail = $request->email && $request->email != $this->email;
        $isBirthDate = $request->birth_date && $request->birth_date != $this->birth_date;

        if ($isFirstName || $isLastName) {

            if ($this->last_time_name_changed &&
                Validation::timeComparison($this->last_time_name_changed, env('PAUSE_BEFORE_CHANGING_NAME'), '<='))
            {
                throw new ApiException(
                    AuthErrorCode::WAIT_BEFORE_CHANGING_NAME()
                );
            }

            if ($isFirstName) {
                $updatedInformation['first_name'] = $request->first_name;
            }

            if ($isLastName) {
                $updatedInformation['last_name'] = $request->last_name;
            }

            $updatedInformation['last_time_name_changed'] = now();
        }

        if ($isEmail) {
            $updatedInformation['email'] = $request->email;
            $updatedInformation['email_verified_at'] = null;
        }

        if ($request->password) {
            $updatedInformation['password'] = $request->password;
            $updatedInformation['last_time_password_changed'] = now();
        }

        if ($isBirthDate) {
            $updatedInformation['birth_date'] = $request->birth_date;
        }

        if ($request->address_coordinates != $this->address_coordinates) {

            if ($request->address_coordinates) {

                $addressCoordinates = explode(';', $request->address_coordinates);

                if (count($addressCoordinates) != 2) {
                    throw new ApiException(
                        BaseErrorCode::FAILED_VALIDATION(),
                        ['address_coordinates' => [__('validation.regex')]]
                    );
                }

                $latitudeLength = strlen($addressCoordinates[0]);
                $longitudeLength = strlen($addressCoordinates[1]);

                if ($latitudeLength != 7 ||
                    $longitudeLength != 7 ||
                    $addressCoordinates[0][2] != '.' ||
                    $addressCoordinates[1][2] != '.')
                {
                    throw new ApiException(
                        BaseErrorCode::FAILED_VALIDATION(),
                        ['address_coordinates' => [__('validation.regex')]]
                    );
                }

                for ($i=0; $i<$latitudeLength; $i++) {
                    if ((!is_numeric($addressCoordinates[0][$i]) ||
                        !is_numeric($addressCoordinates[1][$i])) &&
                        $i != 2)
                    {
                        throw new ApiException(
                            BaseErrorCode::FAILED_VALIDATION(),
                            ['address_coordinates' => [__('validation.regex')]]
                        );
                    }
                }
            }

            $updatedInformation['address_coordinates'] = $request->address_coordinates;
        }

        if ($request->telephone != $this->telephone) {

            if ($request->telephone) {

                $telephoneLength = strlen($request->telephone);

                for ($i=0; $i<$telephoneLength; $i++) {
                    if (!is_numeric($request->telephone[$i])) {
                        throw new ApiException(
                            BaseErrorCode::FAILED_VALIDATION(),
                            ['telephone' => [__('validation.regex')]]
                        );
                    }
                }
            }

            $updatedInformation['telephone'] = $request->telephone;
        }

        if ($request->facebook_profile != $this->facebook_profile) {
            $updatedInformation['facebook_profile'] = $request->facebook_profile;
        }

        if ($request->instagram_profile != $this->instagram_profile) {
            $updatedInformation['instagram_profile'] = $request->instagram_profile;
        }

        if ($request->gender_type_id != $this->gender_type_id) {
            $updatedInformation['gender_type_id'] = $request->gender_type_id;
        }

        if ($updatedInformation) {
            $this->update($updatedInformation);
        }

        $this->refresh();

        return isset($updatedInformation['email']);
    }

    /**
     * Sprawdzenie brakujących informacji o użytkowniku i zwrócenie jego encji
     * 
     * @return void
     */
    public function checkMissingInformation(): void {

        $missingInformation = null;

        if (!$this->email) {
            $missingInformation['required']['email'] = [__('validation.custom.is-missing', ['attribute' => 'adres email'])];
        }

        if (!$this->birth_date) {
            $missingInformation['required']['birth_date'] = [__('validation.custom.is-missing', ['attribute' => 'datę urodzenia'])];
        }

        if (!$this->avatar) {
            $missingInformation['optional']['avatar'] = [__('validation.custom.is-missing', ['attribute' => 'zdjęcie profilowe'])];
        }

        if (!$this->address_coordinates) {
            $missingInformation['optional']['address_coordinates'] = [__('validation.custom.is-missing', ['attribute' => 'lokalizację'])];
        }

        if (!$this->telephone) {
            $missingInformation['optional']['telephone'] = [__('validation.custom.is-missing', ['attribute' => 'numer telefonu'])];
        }

        if (!$this->facebook_profile) {
            $missingInformation['optional']['facebook_profile'] = [__('validation.custom.is-missing', ['attribute' => 'adres profilu na Facebooku'])];
        }

        if (!$this->instagram_profile) {
            $missingInformation['optional']['instagram_profile'] = [__('validation.custom.is-missing', ['attribute' => 'adres profilu na Instagramie'])];
        }

        if (!$this->gender_type_id) {
            $missingInformation['optional']['gender_type_id'] = [__('validation.custom.is-missing', ['attribute' => 'płeć'])];
        }

        if (isset($missingInformation['required']) || !$this->email_verified_at) {
            throw new ApiException(
                $this->email_verified_at ? AuthErrorCode::MISSING_USER_INFORMATION() : AuthErrorCode::UNVERIFIED_EMAIL(),
                ['user' => $this->privateInformation()],
                ['missing_user_information' => $missingInformation]
            );
        }

        JsonResponse::sendSuccess(
            ['user' => $this->privateInformation()],
            ['missing_user_information' => $missingInformation]
        );
    }

    /**
     * Sprawdzenie czy użytkownik może korzystać z serwisu
     * 
     * @return void
     */
    public function checkAccess(): void {

        $accountDeleted = null;
        $accountBlocked = null;

        $accountAction = $this->accountAction()->get();

        foreach ($accountAction as $aA) {
            if (strpos($aA->accountActionType->name, 'ACCOUNT_DELETED') !== false) {
                $accountDeleted = $aA;
            } else if (strpos($aA->accountActionType->name, 'ACCOUNT_BLOCKED') !== false) {
                $accountBlocked = $aA;
            }
        }

        if ($accountBlocked || $accountDeleted) {

            JsonResponse::deleteCookie('JWT');
            JsonResponse::deleteCookie('REFRESH-TOKEN');

            $this->personalAccessToken()->delete();

            if ($accountBlocked) {
                throw new ApiException(
                    AuthErrorCode::ACOUNT_BLOCKED(),
                    [
                        $accountBlocked->accountActionType->description,
                        'Data zniesienia blokady: ' . $accountBlocked->expires_at
                    ]
                );
            }

            if ($accountDeleted) {

                $this->prepareEmail('ACCOUNT_RESTORATION', 'restore-account', MailAccountRestoration::class);

                throw new ApiException(
                    AuthErrorCode::ACOUNT_DELETED(),
                    [
                        $accountDeleted->accountActionType->description,
                        'Wysłaliśmy na Twojego maila link do przywrócenia konta'
                    ]
                );
            }
        }
    }

    /**
     * Utworzenie tokenów uwierzytelniających
     * 
     * @return void
     */
    public function createTokens(): void {

        $encrypter = new Encrypter;
        $refreshToken = $encrypter->generateToken(64, PersonalAccessToken::class, 'refresh_token');
        $encryptedRefreshToken = $encrypter->encrypt($refreshToken);

        $jwtEncryptedName = $encrypter->encrypt('JWT', 3);
        $jwt = $this->createToken($jwtEncryptedName);
        $jwtToken = $jwt->plainTextToken;
        $jwtId = $jwt->accessToken->getKey();

        $this->personalAccessToken()->where('id', $jwtId)->update(['refresh_token' => $encryptedRefreshToken]);

        JsonResponse::setCookie($jwtToken, 'JWT');
        JsonResponse::setCookie($refreshToken, 'REFRESH-TOKEN');
    }

    /**
     * Zweryfikowanie urządzenia i stworzenie odpowiednich logów
     * 
     * @param int $deviceId identyfikator urządzenia
     * @param string $activity nazwa aktywności, która wywołała daną metodę np. LOGIN
     * 
     * @return void
     */
    public function checkDevice(int $deviceId, string $activity): void {

        $encrypter = new Encrypter;
        $encryptedActivity = $encrypter->encrypt($activity, 18);
        $authenticationType = AuthenticationType::where('name', $encryptedActivity)->first();

        $this->authentication()->create([
            'device_id' => $deviceId,
            'authentication_type_id' => $authenticationType->id
        ]);
    }

    /**
     * Utworzenie niezbędnych danych do wysłania maila i wysłanie go
     * 
     * @param string $accountOperation typ przeprowadzanej operacji, np. PASSWORD_RESET
     * @param string $urlEndpoint końcowa nazwa endpointu, dla którego zostanie wygenerowany token np. reset-password
     * @param string $mail klasa maila, który ma zostać wywołany
     * 
     * @return void
     */
    public function prepareEmail(string $accountOperation, string $urlEndpoint, $mail) {

        $accountOperationType = Validation::getAccountOperationType($accountOperation);

        if (!$accountOperationType) {
            throw new ApiException(
                BaseErrorCode::INTERNAL_SERVER_ERROR(),
                'Invalid account operation type.'
            );
        }

        /** @var AccountOperation $accountOperation */
        $accountOperation = $this->accountOperation()->where('account_operation_type_id', $accountOperationType->id)->first();

        $emailSendingCounter = 1;

        if ($accountOperation) {
            $emailSendingCounter += $accountOperation->countMailing();
        }

        $encrypter = new Encrypter;
        $token = $encrypter->generateToken(64, AccountOperation::class);

        $this->accountOperation()->updateOrCreate([],
        [
            'account_operation_type_id' => $accountOperationType->id,
            'token' => $token,
            'email_sending_counter' => $emailSendingCounter
        ]);

        $url = env('APP_URL') . '/' . $urlEndpoint . '?token=' . $token; // TODO Poprawić na prawidłowy URL
        Mail::to($this)->send(new $mail($url));
    }
}
