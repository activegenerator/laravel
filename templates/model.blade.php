namespace {{ $appNamespace }}\Models;

use Illuminate\Database\Eloquent\Model;
@if($yaml->get('config.hasFactory', true))
use Illuminate\Database\Eloquent\Factories\HasFactory;
@endif
@if($yaml->get('config.softDeletes', false))
use Illuminate\Database\Eloquent\SoftDeletes;
@endif
@foreach($usedTypes as $usedType)
use {{ $usedType }};
@endforeach
{!! $yaml->getCode('model.imports') !!}

/**
 * {{ $yaml->getName("Entity") }} Model
 *
@foreach($yaml->fields as $field)
 * @@property {{ $field->type->php }} ${{ $field->slug }},
@endforeach
@foreach($yaml->relations as $relation)
    @if($relation->isSingular())
 * @@property-read {{ $relation->related }} ${{ $relation->prop }},
    @else
 * @@property-read {{ $relation->related }}[] ${{ $relation->prop }},
    @endif
@endforeach
 *
 * @@method static {{ $yaml->getName("Entity") }} create(array $attributes = [])
 */
class {{ $yaml->getName("Entity") }} {!! $yaml->getCode('model.extends', 0, 'extends Model') !!}
{
@if($yaml->get('config.hasFactory', true))
    use HasFactory;
@endif
@if($yaml->get('config.softDeletes', false))
    use SoftDeletes;
@endif
{!! $yaml->getCode('model.header', 4) !!}
    protected $table = '{{ $yaml->table }}';

    protected $fillable = [
@foreach($yaml->fields as $field)
@if($field->fillable)
        '{{ $field->slug }}',
@endif
@endforeach
    ];
{!! '' !!}
    protected $casts = [
@foreach($yaml->fields as $field)
@if($field->casts)
        '{{ $field->slug }}' => '{!! $field->casts !!}',
@endif
@endforeach
    ];

    protected $appends = [
@foreach($yaml->fields as $field)
@if($field->appends)
        '{{ $field->slug }}',
@endif
@endforeach
    ];

    protected $hidden = [
@foreach($yaml->fields as $field)
@if($field->hidden)
        '{{ $field->slug }}',
@endif
@endforeach
    ];

    protected $attributes = [
@foreach($yaml->fields as $field)
@if($field->default)
        '{{ $field->slug }}' => {{ is_string($field->default) ? "'$field->default'" : $field->default }},
@endif
@endforeach
    ];
{!! '' !!}
@foreach($yaml->relations as $relation)
    public function {{ $relation->prop }}() {
        return $this->{{ $relation->is }}({!! $relation->args->display() !!});
    }
    {!! '' !!}
@endforeach

{!! $yaml->getCode('model.body', 4) !!}
}
