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
{!! $yaml->get('code.model.imports') !!}

/**
 * {{ $yaml->getName("Entity") }} Model
 *
@foreach($yaml->fields as $field)
 * @@property {{ $field->type->php }} ${{ $field->slug }},
@endforeach
@foreach($yaml->relations as $relation)
    @if($relation->isSingular())
 * @@property-read {{ $relation->model }} ${{ $relation->prop }},
    @else
 * @@property-read {{ $relation->model }}[] ${{ $relation->prop }},
    @endif
@endforeach
 *
 * @@method static {{ $yaml->getName("Entity") }} create(array $attributes = [])
 */
class {{ $yaml->getName("Entity") }} {!! $yaml->get('code.model.extends', 'extends Model') !!}
{
@if($yaml->get('config.hasFactory', true))
    use HasFactory;
@endif
@if($yaml->get('config.softDeletes', false))
    use SoftDeletes;
@endif
{!! $yaml->get('code.model.header') !!}
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
{!! '' !!}
@foreach($yaml->relations as $relation)
@if(in_array($relation->type, ['belongsTo', 'hasOne', 'hasMany']))
    public function {{ $relation->prop }}() : {{ ucfirst($relation->type) }}  {
        return $this->{{ $relation->type }}({!! $relation->modelOriginal !!}::class, '{{ $relation->field }}');
    }
@endif
@if(in_array($relation->type, ['belongsToMany']))
    public function {{ $relation->prop }}() : {{ ucfirst($relation->type) }}  {
        return $this->{{ $relation->type }}({!! $relation->modelOriginal !!}::class, '{{ $relation->table }}', '{{ $relation->foreignPivotKey }}', '{{ $relation->relatedPivotKey }}');
    }
@endif
{!! '' !!}
@endforeach
{!! $yaml->get('code.model.body') !!}
}
