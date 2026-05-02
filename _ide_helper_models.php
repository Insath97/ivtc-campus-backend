<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property string $module
 * @property string|null $description
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserId($value)
 */
	class ActivityLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $year
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereYear($value)
 */
	class Batch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course> $courses
 * @property-read int|null $courses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $full_name
 * @property \Illuminate\Support\Carbon $starting_date
 * @property \Illuminate\Support\Carbon $ending_date
 * @property string|null $entrol_number
 * @property string|null $course_code
 * @property string $verification_code
 * @property string $certificate_number
 * @property string $nic
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereCertificateNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereCourseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereEndingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereEntrolNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereNic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereStartingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification whereVerificationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certification withoutTrashed()
 */
	class Certification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $page
 * @property string $section
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string|null $label
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent pageContent(string $page)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent wherePage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent whereSection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CmsContent whereValue($value)
 */
	class CmsContent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $subject
 * @property string $message
 * @property string $status
 * @property bool $is_replied
 * @property int|null $replied_by
 * @property \Illuminate\Support\Carbon|null $replied_at
 * @property string|null $reply_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $repliedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereIsReplied($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereRepliedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereRepliedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereReplyMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereUpdatedAt($value)
 */
	class Contact extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string $code
 * @property int $duration
 * @property string $duration_unit
 * @property string $level
 * @property string $medium
 * @property string $short_description
 * @property string $full_description
 * @property bool $show_in_registration
 * @property bool $is_active
 * @property bool $is_new
 * @property bool $has_certificate
 * @property string|null $primary_image
 * @property string|null $fees_structure
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CourseImage> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CourseVideo> $videos
 * @property-read int|null $videos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course forRegistration()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDurationUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereFeesStructure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereFullDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereHasCertificate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereIsNew($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereMedium($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course wherePrimaryImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereShowInRegistration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course withoutTrashed()
 */
	class Course extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $course_id
 * @property string $image_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Course $course
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseImage whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseImage whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseImage whereUpdatedAt($value)
 */
	class CourseImage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $course_id
 * @property int $tag_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseTag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseTag whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseTag whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseTag whereUpdatedAt($value)
 */
	class CourseTag extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $course_id
 * @property string $video_url
 * @property string|null $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Course $course
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseVideo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseVideo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseVideo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseVideo whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseVideo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseVideo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseVideo whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseVideo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseVideo whereVideoUrl($value)
 */
	class CourseVideo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $batch_id
 * @property string $subject_name
 * @property string|null $description
 * @property string $material_type
 * @property string|null $file_path
 * @property string|null $external_url
 * @property string|null $uploaded_date
 * @property bool $is_active
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Batch $batch
 * @property-read \App\Models\User|null $creator
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereExternalUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereMaterialType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereSubjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial whereUploadedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningMaterial withoutTrashed()
 */
	class LearningMaterial extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $batch_id
 * @property string|null $description
 * @property string $paper_file_path
 * @property bool $has_scheme
 * @property string|null $scheme_file_path
 * @property bool $is_active
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Batch $batch
 * @property-read \App\Models\User|null $creator
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper whereHasScheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper wherePaperFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper whereSchemeFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pastpaper withoutTrashed()
 */
	class Pastpaper extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RegistrationProgram> $registrationPrograms
 * @property-read int|null $registration_programs_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pathway whereUpdatedAt($value)
 */
	class Pathway extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $registration_code
 * @property int $pathway_id
 * @property int $program_id
 * @property string $program_type
 * @property string $full_name
 * @property string $nic
 * @property string $dob
 * @property string $gender
 * @property string $phone
 * @property string $email
 * @property string $district
 * @property string $city
 * @property string|null $school_name
 * @property string|null $occupation
 * @property string $status
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pathway $pathway
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $program
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereNic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereOccupation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration wherePathwayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereProgramType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereRegistrationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereSchoolName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Registration withoutTrashed()
 */
	class Registration extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $pathway_id
 * @property string $name
 * @property string $slug
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pathway $pathway
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram search($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram wherePathwayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistrationProgram whereUpdatedAt($value)
 */
	class RegistrationProgram extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereValue($value)
 */
	class SystemSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course> $courses
 * @property-read int|null $courses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereUpdatedAt($value)
 */
	class Tag extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string|null $profile_image
 * @property string $password
 * @property bool $is_active
 * @property bool $can_login
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCanLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfileImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent implements \PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject, \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

