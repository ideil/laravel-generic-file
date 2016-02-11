<?php

namespace Ideil\LaravelGenericFile\Models;

use Ideil\LaravelGenericFile\Traits\EloquentTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Input;

class GenericFile extends \Illuminate\Database\Eloquent\Model
{
    use EloquentTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'generic_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['original_name', 'original_ext', 'name', 'ext', 'hash'];

    /**
     * Constructor method.
     *
     * @param array|string|UploadedFile $attributes
     */
    public function __construct($attributes = array())
    {
        if ($attributes instanceof UploadedFile) {
            parent::__construct([]);

            $this->upload($attributes);
        } elseif (is_string($attributes)) {
            parent::__construct([]);

            $this->upload(Input::file($attributes));
        } else {
            parent::__construct($attributes);
        }
    }
}
