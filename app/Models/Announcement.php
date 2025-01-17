<?php

namespace App\Models;

use App\Exceptions\ApiException;
use App\Http\ErrorCodes\BaseErrorCode;
use App\Http\Libraries\FileProcessing\FileProcessing;
use App\Http\Libraries\Validation\Validation;
use App\Http\Responses\JsonResponse;
use App\Http\Traits\Encryptable;
use Illuminate\Support\Facades\Auth;
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

    protected $filters = [
        'sort',
        'like',
        'in',
        'greater',
        'greater_or_equal',
        'less',
        'less_or_equal',
        'date_from',
        'date_to',
        'search'
    ];

    public function date_from($query, $value) {
        $query->where('start_date', '>=', $value . ' 00:00:00');
    }

    public function date_to($query, $value) {
        $query->where('start_date', '<=', $value . ' 23:59:59');
    }

    public function search($query, $value) {
        $query->whereHas('facility', function ($q) use ($value) {
            $q->where('name', 'like', '%' . $value . '%')
                ->orWhere('street', 'like', '%' . $value . '%')
                ->orWhereHas('city', function ($q2) use ($value) {
                    $q2->where('name', 'like', '%' . $value . '%')
                        ->orWhereHas('parent', function ($q3) use ($value) {
                            $q3->where('name', 'like', '%' . $value . '%')
                                ->orWhereHas('parent', function ($q4) use ($value) {
                                    $q4->where('name', 'like', '%' . $value . '%')
                                        ->orWhereHas('parent', function ($q5) use ($value) {
                                            $q5->where('name', 'like', '%' . $value . '%')
                                                ->orWhereHas('parent', function ($q6) use ($value) {
                                                    $q6->where('name', 'like', '%' . $value . '%');
                                                });
                                        });
                                });
                        });
                });
        });
    }

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
                'id' => (int) $announcementImage->id,
                'filename' => '/storage/partner-pictures/' . $image->filename
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

        $user = Auth::user();

        $imageType = Validation::getDefaultType('ANNOUNCEMENT_IMAGE', 'IMAGE_TYPE');

        /** @var ImageAssignment $oldImage */
        $oldImage = $this->imageAssignments()->where('image_type_id', $imageType->id)->first();

        if ($oldImage) {
            Storage::delete('partner-pictures/' . $oldImage->image()->first()->filename);
            $oldImage->image()->first()->delete();
        }

        $image = FileProcessing::saveAnnouncementImage($imagePath, $this);

        $imageAssignment = new ImageAssignment;
        $imageAssignment->imageable_type = 'App\Models\Announcement';
        $imageAssignment->imageable_id = $this->id;
        $imageAssignment->image_type_id = $imageType->id;
        $imageAssignment->image_id = $image->id;
        $imageAssignment->number = 1;
        $imageAssignment->creator_id = $user->id;
        $imageAssignment->editor_id = $user->id;
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
     * Zwrócenie minimalnych informacji o wydarzeniu
     * 
     * @return array
     */
    public function getMinInformation(): array {

        /** @var PartnerSetting $partner */
        $partner = $this->announcementPartner()->first();

        /** @var Facility $facility */
        $facility = $this->facility()->first();

        if ($facility && $facility->address_coordinates) {
            $addressCoordinates = $facility->address_coordinates;
            $addressCoordinates = explode(';', $addressCoordinates);
        }

        /** @var DefaultType $sport */
        $sport = $this->sport()->first();

        return [
            'announcement' => [
                'id' => (int) $this->id,
                'sport' => [
                    'id' => (int) $sport->id,
                    'name' => $sport->name,
                    'description' => $sport->description_simple,
                    'icon' => $sport->icon()->first() ? $sport->icon()->first()->filename : null,
                    'color' => $sport->color,
                ],
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'ticket_price' => (int) $this->ticket_price,
                'minimum_skill_level' => $this->minimumSkillLevel()->first() ? [
                    'id' => (int) $this->minimumSkillLevel()->first()->id,
                    'name' => $this->minimumSkillLevel()->first()->name,
                ] : null,
                'participants_counter' => (int) $this->participants_counter,
                'maximum_participants_number' => (int) $this->maximum_participants_number,
                'front_image' => $this->getImage(),
            ],
            'facility' => $facility ? [
                'id' => (int) $facility->id,
                'name' => $facility->name,
                'street' => $facility->street,
                'city' => $facility->city()->first() ? [
                    'id' => (int) $facility->city()->first()->id,
                    'name' => $facility->city()->first()->name,
                ] : null,
                'address_coordinates' => [
                    'lat' => isset($addressCoordinates) ? (float) $addressCoordinates[0] : null,
                    'lng' => isset($addressCoordinates) ? (float) $addressCoordinates[1] : null
                ]
            ]: null
        ];
    }

    /**
     * Zwrócenie podstawowych informacji o wydarzeniu
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
        foreach ($this->announcementSeats()->get() as $aS) {
            $announcementSeats[] = [
                'id' => (int) $aS->id,
                'sports_position' => [
                    'id' => (int) $aS->sportsPosition()->first()->id,
                    'name' => $aS->sportsPosition()->first()->name,
                ],
                'occupied_seats_counter' => (int) $aS->occupied_seats_counter,
                'maximum_seats_number' => (int) $aS->maximum_seats_number,
                'is_active' => (bool) $aS->is_active
            ];

            /** @var User $itsMe */
            $itsMe = Auth::user();

            /** @var AnnouncementParticipant $aP */
            foreach ($aS->announcementParticipants()->get() as $aP) {
                /** @var User $user */
                $user = $aP->user()->first();
                if ($aP->joiningStatus()->first()->id == 91) {
                    $announcementParticipants[] = [
                        'id' => (int) $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'gender' => $user->getGender(),
                        'avatar' => $user->getAvatars(),
                        'its_me' => $itsMe && $user && $itsMe->id == $user->id ? true : false,
                        'status' => [
                            'id' => (int) $aP->joiningStatus()->first()->id,
                            'name' => $aP->joiningStatus()->first()->name,
                        ],
                        'announcement_seat' => [
                            'id' => (int) $aP->announcement_seat_id
                        ]
                    ];
                }
            }
        }

        /** @var AnnouncementPayment $aP */
        foreach ($this->announcementPayments()->get() as $aP) {
            $announcementPayments[] = [
                'id' => (int) $aP->id,
                'payment_type' => [
                    'id' => (int) $aP->paymentType()->first()->id,
                    'name' => $aP->paymentType()->first()->name,
                    'description' => $aP->paymentType()->first()->description_simple
                ],
                'is_active' => (bool) $aP->is_active
            ];
        }

        if ($facility && $facility->address_coordinates) {
            $addressCoordinates = $facility->address_coordinates;
            $addressCoordinates = explode(';', $addressCoordinates);
        }

        /** @var DefaultType $sport */
        $sport = $this->sport()->first();

        $comments = null;

        /** @var Rating[] $announcementComment */
        $announcementComment = Rating::where('evaluable_type', 'App\Models\Announcement')->where('evaluable_id', $this->id)->get();

        /** @var Rating $aC */
        foreach ($announcementComment as $aC) {

            /** @var User $commentedUser */
            $commentedUser = $aC->evaluator()->first();

            if (!$aC->answer_to_id) {
                $comments[] = [
                    'id' => $aC->id,
                    'comment' => $aC->comment,
                    'date' => $aC->created_at,
                    'user' => [
                        'id' => $commentedUser->id,
                        'name' => $commentedUser->first_name . ' ' . $commentedUser->last_name,
                        'gender' => $commentedUser->getGender(),
                        'avatar' => $commentedUser->getAvatars(),
                        'its_me' => $itsMe && $itsMe->id == $commentedUser->id ? true : false,
                    ]
                ];
            } else {
                if ($comments) {
                    foreach ($comments as &$c) {
                        if ($c['id'] == $aC->answer_to_id) {
                            $c['answers'][] = [
                                'id' => $aC->id,
                                'comment' => $aC->comment,
                                'date' => $aC->created_at,
                                'user' => [
                                    'id' => $commentedUser->id,
                                    'name' => $commentedUser->first_name . ' ' . $commentedUser->last_name,
                                    'gender' => $commentedUser->getGender(),
                                    'avatar' => $commentedUser->getAvatars(),
                                    'its_me' => $itsMe->id == $commentedUser->id ? true : false,
                                ]
                            ];
                            break;
                        }
                    }
                }
            }
        }

        return [
            'partner' => $partner->getPartner('getBasicInformation', true, $this),
            'announcement' => [
                'id' => (int) $this->id,
                'sport' => [
                    'id' => (int) $sport->id,
                    'name' => $sport->name,
                    'description' => $sport->description_simple,
                    'icon' => $sport->icon()->first() ? $sport->icon()->first()->filename : null,
                    'color' => $sport->color,
                ],
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'ticket_price' => (int) $this->ticket_price,
                'game_variant' => [
                    'id' => (int) $this->gameVariant()->first()->id,
                    'name' => $this->gameVariant()->first()->name,
                ],
                'minimum_skill_level' => $this->minimumSkillLevel()->first() ? [
                    'id' => (int) $this->minimumSkillLevel()->first()->id,
                    'name' => $this->minimumSkillLevel()->first()->name,
                ] : null,
                'gender' => $this->gender()->first() ? [
                    'id' => (int) $this->gender()->first()->id,
                    'name' => $this->gender()->first()->name,
                ] : null,
                // 'age_category' => $this->ageCategory()->first() ? [
                //     'id' => (int) $this->ageCategory()->first()->id,
                //     'name' => $this->ageCategory()->first()->name,
                // ] : null,
                // 'minimal_age' => (int) $this->minimal_age,
                // 'maximum_age' => (int) $this->maximum_age,
                'description' => $this->description,
                'participants_counter' => (int) $this->participants_counter,
                'maximum_participants_number' => (int) $this->maximum_participants_number,
                // 'announcement_type' => $this->announcementType()->first() ? [
                //     'id' => (int) $this->announcementType()->first()->id,
                //     'name' => $this->announcementType()->first()->name,
                // ] : null,
                'announcement_status' => $this->announcementStatus()->first() ? [
                    'id' => (int) $this->announcementStatus()->first()->id,
                    'name' => $this->announcementStatus()->first()->name,
                ] : null,
                // 'is_automatically_approved' => (bool) $this->is_automatically_approved,
                'is_public' => (bool) $this->is_public,
                'front_image' => $this->getImage(),
                'background_image' => '/storage/partner-pictures/volleyball-background.jpeg',
                'announcement_seats' => $announcementSeats,
                'announcement_payments' => $announcementPayments,
                'announcement_participants' => $announcementParticipants,
                'comments' => $comments
            ],
            'facility' => $facility ? [
                'id' => (int) $facility->id,
                'name' => $facility->name,
                'street' => $facility->street,
                'city' => $facility->city()->first() ? [
                    'id' => (int) $facility->city()->first()->id,
                    'name' => $facility->city()->first()->name,
                ] : null,
                'address_coordinates' => [
                    'lat' => isset($addressCoordinates) ? (float) $addressCoordinates[0] : null,
                    'lng' => isset($addressCoordinates) ? (float) $addressCoordinates[1] : null
                ]
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
