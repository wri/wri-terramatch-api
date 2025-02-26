<?php

namespace App\Models\V2\Demographics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemographicEntry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'demographic_id',
        'type',
        'subtype',
        'name',
        'amount',
    ];

    // demographic types
    public const GENDER = 'gender';
    public const GENDERS = ['male', 'female', 'non-binary', 'unknown'];
    public const AGE = 'age';
    public const AGES = ['youth', 'non-youth', 'adult', 'elder', 'unknown'];
    public const ETHNICITY = 'ethnicity';
    public const ETHNICITIES = ['indigenous', 'other', 'unknown'];
    public const CASTE = 'caste';
    public const CASTES = ['marginalized'];

    public function demographic()
    {
        return $this->belongsTo(Demographic::class);
    }

    public function scopeGender(Builder $query): Builder
    {
        return $query->where('type', self::GENDER);
    }

    public function scopeIsGender(Builder $query, string $gender): Builder
    {
        return $query->where(['type' => self::GENDER, 'subtype' => $gender]);
    }

    public function scopeAge(Builder $query): Builder
    {
        return $query->where('type', self::AGE);
    }

    public function scopeIsAge(Builder $query, string $age): Builder
    {
        return $query->where(['type' => self::AGE, 'subtype' => $age]);
    }

    public function scopeEthnicity(Builder $query): Builder
    {
        return $query->where('type', self::ETHNICITY);
    }

    public function scopeIsEthnicity(Builder $query, string $ethnicity, string $name = null): Builder
    {
        return $query->where(['type' => self::ETHNICITY, 'subtype' => $ethnicity, 'name' => $name]);
    }

    public function scopeCaste(Builder $query): Builder
    {
        return $query->where('type', self::CASTE);
    }
}
