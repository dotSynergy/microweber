<?php

namespace Modules\Teamcard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MicroweberPackages\Database\Casts\ReplaceSiteUrlCast;
use MicroweberPackages\Database\Traits\CacheableQueryBuilderTrait;
use MicroweberPackages\Database\Traits\MaxPositionTrait;
use MicroweberPackages\Multilanguage\Models\Traits\HasMultilanguageTrait;

class Teamcard extends Model
{
    use HasFactory;
    use HasMultilanguageTrait;
    use CacheableQueryBuilderTrait;
    use MaxPositionTrait;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'file',
        'bio',
        'role',
        'website',
        'position',
        'rel_type',
        'rel_id',
        'settings',
    ];

    public $translatable = ['name', 'bio', 'role'];

    protected $casts = [
        'settings' => 'array',
        'file' => ReplaceSiteUrlCast::class, //Casts like that: http://lorempixel.com/400/200/ =>  {SITE_URL}400/200/
    ];
}
