namespace Database\Factories;
use App\Models\{{ $model->getName('Entity') }};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
{!! $model->getCode('factory.imports', 0) !!}

class {{ $model->getName('Entity') }}Factory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = {{ $model->getName('Entity') }}::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
@foreach($model->fields as $field)
    @if($field->slug == "created_at" || $field->slug == "updated_at")@continue @endif
    @if($field->type->database != "id" && $field->type->database != "foreignId" && $field->slug != "deleted_at")
        @if($field->type->fakerMethod)
          '{{ $field->slug }}' => $this->faker->{{ $field->type->fakerMethod }},
        @elseif($field->type->example)
          '{{ $field->slug }}' => {!!$field->type->example!!},
        @endif
    @endif
    @if($field->type->database == "foreignId")
          '{{ $field->slug }}' => {{ $field->type->relatedRelation()->relatedFull }}::factory(),
    @endif
@endforeach
{!! $model->getCode('factory.definition', 10) !!}
        ];
    }
}
