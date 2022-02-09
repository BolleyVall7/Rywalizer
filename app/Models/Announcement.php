<?php

namespace App\Models;

use App\Exceptions\ApiException;
use App\Http\ErrorCodes\BaseErrorCode;
use App\Http\Libraries\FileProcessing\FileProcessing;
use App\Http\Libraries\Validation\Validation;
use App\Http\Responses\JsonResponse;
use App\Http\Traits\Encryptable;
use Illuminate\Support\Facades\Storage;

class Announcement extends BaseModel
{
    use Encryptable;

    protected $fillable = [
        'announcement_partner_id',
        'facility_id',
        'sport_id',
        'start_date',
        'end_date',
        'visible_at',
        'ticket_price',
        'game_variant_id',
        'minimum_skill_level_id',
        'gender_id',
        'age_category_id',
        'minimal_age',
        'maximum_age',
        'description',
        'maximum_participants_number',
        'announcement_type_id',
        'announcement_status_id',
        'is_automatically_approved',
        'is_public'
    ];

    protected $guarded = [
        'id',
        'code',
        'participants_counter',
        'creator_id',
        'editor_id',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'id',
        'announcement_partner_id',
        'facility_id',
        'sport_id',
        'start_date',
        'end_date',
        'visible_at',
        'ticket_price',
        'game_variant_id',
        'minimum_skill_level_id',
        'gender_id',
        'age_category_id',
        'minimal_age',
        'maximum_age',
        'code',
        'description',
        'participants_counter',
        'maximum_participants_number',
        'announcement_type_id',
        'announcement_status_id',
        'creator_id',
        'editor_id',
        'is_automatically_approved',
        'is_public',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'start_date' => 'string',
        'end_date' => 'string',
        'visible_at' => 'string',
        'ticket_price' => 'int',
        'minimal_age' => 'int',
        'maximum_age' => 'int',
        'participants_counter' => 'int',
        'maximum_participants_number' => 'int',
        'is_automatically_approved' => 'boolean',
        'is_public' => 'boolean',
        'created_at' => 'string',
        'updated_at' => 'string'
    ];

    protected $encryptable = [
        'code' => 9,
        'description' => 1500
    ];

    public function announcementPartner() {
        return $this->belongsTo(PartnerSetting::class, 'announcement_partner_id');
    }

    public function facility() {
        return $this->belongsTo(Facility::class);
    }

    public function sport() {
        return $this->belongsTo(DefaultType::class, 'sport_id');
    }

    public function gameVariant() {
        return $this->belongsTo(DefaultType::class, 'game_variant_id');
    }

    public function minimumSkillLevel() {
        return $this->belongsTo(MinimumSkillLevel::class);
    }

    public function gender() {
        return $this->belongsTo(DefaultType::class, 'gender_id');
    }

    public function ageCategory() {
        return $this->belongsTo(DefaultType::class, 'age_category_id');
    }

    public function announcementType() {
        return $this->belongsTo(DefaultType::class, 'announcement_type_id');
    }

    public function announcementStatus() {
        return $this->belongsTo(DefaultType::class, 'announcement_status_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function editor() {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function imageAssignments() {
        return $this->morphMany(ImageAssignment::class, 'imageable');
    }

    public function actionables() {
        return $this->morphMany(AccountAction::class, 'actionable');
    }

    public function discountable() {
        return $this->morphMany(Discount::class, 'discountable');
    }

    public function contractable() {
        return $this->morphMany(Agreement::class, 'contractable');
    }

    public function reportable() {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function announcementPayments() {
        return $this->hasMany(AnnouncementPayment::class);
    }

    public function announcementSeats() {
        return $this->hasMany(AnnouncementSeat::class);
    }

    /**
     * Zwrócenie zdjęcia w tle wydarzenia
     * 
     * @return array|null
     */
    public function getImage(): ?array {

        $defaultType = Validation::getDefaultType('ANNOUNCEMENT_IMAGE', 'IMAGE_TYPE');

        $result = null;

        /** @var ImageAssignment $announcementImage */
        $announcementImage = $this->imageAssignments()->where('image_type_id', $defaultType->id)->orderBy('number', 'desc')->first();

        if ($announcementImage) {
            /** @var Image $image */
            $image = $announcementImage->image()->first();

            $result[] = [
                'id' => $announcementImage->id,
                'filename' => $image->filename
            ];
        }

        return $result;
    }

    /**
     * Zapisanie zdjęcia w tle
     * 
     * @param string $imagePath aktualna ścieżka do loga
     * 
     * @return void
     */
    public function savePhoto(string $imagePath): void {

        $imageType = Validation::getDefaultType('ANNOUNCEMENT_IMAGE', 'IMAGE_TYPE');

        $oldImages = $this->imageAssignments()->where('image_type_id', $imageType->id)->orderBy('number', 'desc')->get();

        $counter = 0;

        foreach ($oldImages as $oI) {
            $counter++;
        }

        $newNumber = $counter + 1;

        foreach ($oldImages as $oI) {
            $oI->number = $counter;
            $oI->save();
            $counter--;
        }

        $image = FileProcessing::saveAnnouncementImage($imagePath, $this);

        $imageAssignment = new ImageAssignment;
        $imageAssignment->imageable_type = 'App\Models\Announcement';
        $imageAssignment->imageable_id = $this->id;
        $imageAssignment->image_type_id = $imageType->id;
        $imageAssignment->image_id = $image->id;
        $imageAssignment->number = $newNumber;
        $imageAssignment->creator_id = $this->id;
        $imageAssignment->editor_id = $this->id;
        $imageAssignment->save();
    }

    /**
     * Usunięcie loga partnera
     * 
     * @return void
     */
    public function deletePhoto(int $imageId): void {

        $imageType = Validation::getDefaultType('ANNOUNCEMENT_IMAGE', 'IMAGE_TYPE');

        /** @var ImageAssignment $imageAssignment */
        $imageAssignment = $this->imageAssignments()->where('image_type_id', $imageType->id)->where('id', $imageId)->first();

        if ($imageAssignment) {
            Storage::delete('partner-pictures/' . $imageAssignment->image()->first()->filename);
            $imageAssignment->image()->first()->delete();
        } else {
            throw new ApiException(
                BaseErrorCode::FAILED_VALIDATION(),
                'Podano nieprawidłowy identyfikator zdjęcia'
            );
        }
    }

    /**
     * Zwrócenie podstawowych informacji o partnerze
     * 
     * @return array
     */
    public function getBasicInformation(): array {

        /** @var PartnerSetting $partner */
        $partner = $this->announcementPartner()->first();

        /** @var Facility $facility */
        $facility = $this->facility()->first();

        $announcementSeats = [];
        $announcementPayments = [];
        $announcementParticipants = [];

        /** @var AnnouncementSeat $aS */
        foreach ($this->announcementSeats() as $aS) {
            $announcementSeats[] = [
                'id' => $aS->id,
                'sports_position' => [
                    'id' => $aS->sportsPosition()->first()->id,
                    'name' => $aS->sportsPosition()->first()->name,
                ],
                'occupied_seats_counter' => $aS->occupied_seats_counter,
                'maximum_seats_number' => $aS->maximum_seats_number,
                'is_active' => $aS->is_active
            ];

            /** @var AnnouncementParticipant $aP */
            foreach ($aS->announcementParticipants() as $aP) {
                /** @var User $user */
                $user = $aP->user()->first();
                $announcementParticipants[] = [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'gender' => $user->getGender(),
                    'avatar' => $user->getAvatars()
                ];
            }
        }

        /** @var AnnouncementPayment $aP */
        foreach ($this->announcementPayments() as $aP) {
            $announcementPayments[] = [
                'id' => $aP->id,
                'payment_type' => [
                    'id' => $aP->paymentType()->first()->id,
                    'name' => $aP->paymentType()->first()->name,
                ],
                'is_active' => $aP->is_active
            ];
        }

        return [
            'partner' => $partner->getPartner('getBasicInformation', true),
            'announcement' => [
                'id' => $this->id,
                'sport' => [
                    'id' => $this->sport()->first()->id,
                    'name' => $this->sport()->first()->name,
                ],
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'ticket_price' => $this->ticket_price,
                'game_variant' => [
                    'id' => $this->gameVariant()->first()->id,
                    'name' => $this->gameVariant()->first()->name,
                ],
                'minimum_skill_level' => $this->minimumSkillLevel()->first() ? [
                    'id' => $this->minimumSkillLevel()->first()->id,
                    'name' => $this->minimumSkillLevel()->first()->name,
                ] : null,
                'gender' => $this->gender()->first() ? [
                    'id' => $this->gender()->first()->id,
                    'name' => $this->gender()->first()->name,
                ] : null,
                'age_category' => $this->ageCategory()->first() ? [
                    'id' => $this->ageCategory()->first()->id,
                    'name' => $this->ageCategory()->first()->name,
                ] : null,
                'minimal_age' => $this->minimal_age,
                'maximum_age' => $this->maximum_age,
                'description' => $this->description,
                'participants_counter' => $this->participants_counter,
                'maximum_participants_number' => $this->maximum_participants_number,
                'announcement_type' => $this->announcementType()->first() ? [
                    'id' => $this->announcementType()->first()->id,
                    'name' => $this->announcementType()->first()->name,
                ] : null,
                'announcement_status' => $this->announcementStatus()->first() ? [
                    'id' => $this->announcementStatus()->first()->id,
                    'name' => $this->announcementStatus()->first()->name,
                ] : null,
                'is_automatically_approved' => $this->is_automatically_approved,
                'is_public' => $this->is_public,
                'image' => $this->getImage()
            ],
            'announcement_seats' => $announcementSeats,
            'announcement_payments' => $announcementPayments,
            'announcement_participants' => $announcementParticipants,
            'facility' => $facility ? [
                'id' => $facility->id,
                'name' => $facility->name,
                'street' => $facility->street,
                'city' => [
                    'id' => $facility->city()->first()->id,
                    'name' => $facility->city()->first()->name,
                ],
            ]: null
        ];
    }

    /**
     * Zwrócenie informacji o wydarzeniu
     * 
     * @param string $modelMethodName nazwa metody, która ma zostać dołączona jako wykaz zwróconych pól wydarzenia, np. getPrivateInformation
     * 
     * @return void
     */
    public function getAnnouncement($modelMethodName): void {
        JsonResponse::sendSuccess($this->$modelMethodName());
    }
}
