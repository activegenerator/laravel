namespace {{ $appNamespace }}\Models;

use Illuminate\Database\Eloquent\Model;
@if($model->get('config.hasFactory', true))
use Illuminate\Database\Eloquent\Factories\HasFactory;
@endif
@if($model->get('config.softDeletes', false))
use Illuminate\Database\Eloquent\SoftDeletes;
@endif
@foreach($usedTypes as $usedType)
use {{ $usedType }};
@endforeach
{!! $model->getCode('model.imports') !!}

/**
 * {{ $model->getName("Entity") }} Model
 *
@foreach($model->fields as $field)
 * @@property {{ $field->type->php }} ${{ $field->slug }},
@endforeach
@foreach($model->relations as $relation)
    @if($relation->isSingular())
 * @@property-read {{ $relation->related }} ${{ $relation->prop }},
    @else
 * @@property-read {{ $relation->related }}[] ${{ $relation->prop }},
    @endif
@endforeach
 *
 * @@method static {{ $model->getName("Entity") }} create(array $attributes = [])
 */
class {{ $model->getName("Entity") }} {!! $model->getCode('model.extends', 0, 'extends Model') !!}
{
@if($model->get('config.hasFactory', true))
    use HasFactory;
@endif
@if($model->get('config.softDeletes', false))
    use SoftDeletes;
@endif
{!! $model->getCode('model.header', 4) !!}
    protected $table = '{{ $model->table }}';

    protected $fillable = [
@foreach($model->fields as $field)
@if($field->fillable)
        '{{ $field->slug }}',
@endif
@endforeach
    ];
{!! '' !!}
    protected $casts = [
@foreach($model->fields as $field)
@if($field->casts)
        '{{ $field->slug }}' => '{!! $field->casts !!}',
@endif
@endforeach
    ];

    protected $appends = [
@foreach($model->fields as $field)
@if($field->appends)
        '{{ $field->slug }}',
@endif
@endforeach
    ];

    protected $hidden = [
@foreach($model->fields as $field)
@if($field->hidden)
        '{{ $field->slug }}',
@endif
@endforeach
    ];

    protected $attributes = [
@foreach($model->fields as $field)
@if($field->default)
        '{{ $field->slug }}' => {{ is_string($field->default) ? "'$field->default'" : $field->default }},
@endif
@endforeach
    ];
{!! '' !!}
@foreach($model->relations as $relation)
    public function {{ $relation->prop }}() {
        return $this->{{ $relation->is }}({!! $relation->args->display() !!});
    }
    {!! '' !!}
@endforeach

{!! $model->getCode('model.body', 4) !!}
}
